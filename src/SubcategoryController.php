<?php
// src/SubcategoryController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auditor.php';
require_once __DIR__ . '/../vendor/autoload.php';        // TCPDF via Composer
require_once __DIR__ . '/Reports/SubcategoryPdf.php';    // Nuestra clase

class SubcategoryController {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** * Listado con nombre de categoría (join) + búsqueda opcional, con Paginación (MODIFICADO)
     * @param string $q El término de búsqueda.
     * @param ?int $cat El ID de la categoría para filtrar.
     * @param int $page La página actual (base 1).
     * @param int $limit El número de registros por página.
     * @return array ['total_records' => int, 'data' => array]
     */
    public function list(string $q = '', ?int $cat = null, int $page = 1, int $limit = 10): array {
        // 1. Consulta base para contar y para obtener datos
        $baseSql = "FROM subcategorias s INNER JOIN categorias c ON c.id_categoria = s.id_categoria";
        $where = [];
        $params = [];
        
        if ($q !== '') {
            $where[] = "(s.nombre LIKE :q OR s.descripcion LIKE :q2)";
            $params[':q'] = "%$q%";
            $params[':q2'] = "%$q%";
        }
        if ($cat !== null && $cat > 0) {
            $where[] = "s.id_categoria = :cat";
            $params[':cat'] = $cat;
        }
        $whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";
        
        // 2. Obtener el total de registros (sin LIMIT/OFFSET)
        $stCount = $this->db->prepare("SELECT COUNT(*) " . $baseSql . $whereSql);
        $stCount->execute($params);
        $totalRecords = (int)$stCount->fetchColumn();

        // 3. Preparar la consulta para los datos de la página
        $offset = ($page - 1) * $limit;
        
        $dataSql = "SELECT s.id_subcategoria, s.id_categoria, c.nombre AS categoria,
                       s.nombre, s.descripcion, s.activa, s.creada_en, s.actualizada_en "
                 . $baseSql
                 . $whereSql
                 . " ORDER BY s.creada_en DESC"
                 . " LIMIT :limit OFFSET :offset";

        $st = $this->db->prepare($dataSql);
        
        // Asignar parámetros para la consulta de datos
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $st->execute();

        return [
            'total_records' => $totalRecords,
            'data' => $st->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    /** Para llenar el combo de categorías */
    public function categories(): array {
        $st = $this->db->query("SELECT id_categoria, nombre FROM categorias WHERE activa = 1 ORDER BY nombre");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): array {
        $st = $this->db->prepare("SELECT * FROM subcategorias WHERE id_subcategoria = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Subcategoría no encontrada');
        return $row;
    }

    // AÑADIDO: $userId
    public function create(int $id_categoria, string $nombre, ?string $descripcion, int $activa, ?int $userId): int {
        if ($id_categoria <= 0) throw new InvalidArgumentException('Selecciona una categoría.');
        if (trim($nombre) === '') throw new InvalidArgumentException('El nombre es obligatorio.');
        
        $st = $this->db->prepare(
            "INSERT INTO subcategorias (id_categoria, nombre, descripcion, activa)
             VALUES (:id_categoria, :nombre, :descripcion, :activa)"
        );
        $st->execute([
            ':id_categoria' => $id_categoria,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0
        ]);
        $id = (int)$this->db->lastInsertId();

        // AUDITORÍA
        Auditor::log($userId, 'subcategorias', $id, 'CREATE', [
            'id_categoria' => $id_categoria, 
            'nombre' => $nombre, 
            'activa' => $activa
        ]);
        
        return $id;
    }

    // AÑADIDO: $userId
    public function update(int $id, int $id_categoria, string $nombre, ?string $descripcion, int $activa, ?int $userId): bool {
        $old_data = $this->find($id); // Obtener datos antes de actualizar
        if ($id_categoria <= 0) throw new InvalidArgumentException('Selecciona una categoría.');
        if (trim($nombre) === '') throw new InvalidArgumentException('El nombre es obligatorio.');
        
        $st = $this->db->prepare(
            "UPDATE subcategorias
               SET id_categoria = :id_categoria,
                   nombre = :nombre,
                   descripcion = :descripcion,
                   activa = :activa
             WHERE id_subcategoria = :id"
        );
        $success = $st->execute([
            ':id_categoria' => $id_categoria,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
        
        if ($success) {
            // AUDITORÍA: Comparar y registrar solo los cambios
            $new_data = ['id_categoria' => $id_categoria, 'nombre' => $nombre, 'descripcion' => $descripcion, 'activa' => $activa];
            $changes = [];
            foreach ($new_data as $key => $value) {
                // Manejar la diferencia de tipos para comparación
                $old_val = $old_data[$key];
                $new_val = in_array($key, ['id_categoria', 'activa']) ? (int)$value : (string)$value;
                
                if ((string)$old_val !== (string)$new_val) {
                    $changes[$key] = ['old' => $old_data[$key], 'new' => $new_val];
                }
            }
            
            if (!empty($changes)) {
                 Auditor::log($userId, 'subcategorias', $id, 'UPDATE', $changes);
            }
        }
        
        return $success;
    }

    /**
     * Elimina una subcategoría, verificando que no esté en uso por productos (MODIFICADO).
     * @param int $id ID de la subcategoría a eliminar.
     * @param ?int $userId ID del usuario que realiza la acción.
     * @return bool
     * @throws RuntimeException Si la subcategoría está relacionada con productos.
     */
    public function delete(int $id, ?int $userId): bool {
        // 1. VERIFICACIÓN DE INTEGRIDAD REFERENCIAL (NUEVO)
        $stCheck = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE id_subcategoria = :id");
        $stCheck->execute([':id' => $id]);
        if ((int)$stCheck->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar la subcategoría porque ya está vinculada a uno o más productos.');
        }

        // 2. Proceso de eliminación
        $data = $this->find($id); // Obtener datos antes de eliminar
        $st = $this->db->prepare("DELETE FROM subcategorias WHERE id_subcategoria = :id");
        $success = $st->execute([':id' => $id]);
        
        if ($success) {
            // AUDITORÍA
            Auditor::log($userId, 'subcategorias', $id, 'DELETE', [
                'registro_eliminado' => $data['nombre'] ?? 'ID ' . $id,
                'id_categoria' => $data['id_categoria']
            ]);
        }
        
        return $success;
    }
}

/* --- Router JSON/PDF (MODIFICADO para paginación) --- */
if (php_sapi_name() !== 'cli') {
    // IMPORTANTE: Requerir Auth para obtener el ID del usuario
    require_once __DIR__ . '/Auth.php';
    $auth = new Auth();
    $user = $auth->check() ? $auth->user() : null;
    $userId = $user['id'] ?? null; // ID del usuario logueado
    
    try {
        $ctrl = new SubcategoryController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                header('Content-Type: application/json; charset=utf-8');
                $q = trim((string)($_GET['q'] ?? ''));
                $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
                // NUEVO: Paginación
                $page  = (int)($_GET['page'] ?? 1);
                $limit = (int)($_GET['limit'] ?? 10);
                $page = max(1, $page);
                $limit = max(1, $limit); 

                $result = $ctrl->list($q, $cat, $page, $limit);

                echo json_encode(['ok'=>true, 'data'=>$result['data'], 'total_records' => $result['total_records']], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'categories': {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>true,'data'=>$ctrl->categories()], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'find': {
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['ok'=>true,'data'=>$ctrl->find($id)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'create': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id_categoria = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($id_categoria,$nombre,$descripcion,$activa, $userId); 
                echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_subcategoria'] ?? 0);
                $id_categoria = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id,$id_categoria,$nombre,$descripcion,$activa, $userId); 
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_subcategoria'] ?? 0);
                // Si la subcategoría está vinculada a un producto, delete() lanzará una RuntimeException
                $ctrl->delete($id, $userId); 
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'export_pdf': {
                // IMPORTANTE: sin header JSON y limpiar buffer antes del binario
                if (ob_get_length()) { ob_end_clean(); }

                $q   = trim((string)($_GET['q'] ?? ''));
                $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : null;

                // Obtener todos los registros filtrados (sin límite de paginación)
                $rows = $ctrl->list($q, $cat, 1, 999999)['data']; 

                // Armamos texto de filtro para el encabezado
                $filtroParts = [];
                if ($q !== '')   { $filtroParts[] = "Texto: {$q}"; }
                if ($cat && $cat > 0) {
                    // Resolvemos nombre de la categoría para que sea legible en el PDF
                    $cats = $ctrl->categories();
                    foreach ($cats as $c) {
                        if ((int)$c['id_categoria'] === (int)$cat) {
                            $filtroParts[] = "Categoría: {$c['nombre']}";
                            break;
                        }
                        // Si la categoría no está activa, aún debemos mostrarla en el filtro del PDF
                    }
                }
                $filtroTxt = $filtroParts ? implode(' | ', $filtroParts) : null;

                $empresa = 'SIRIO S.A. DE C.V.';
                $report  = new SubcategoryPdf('Subcategorías - Reporte');
                $report->output($rows, $empresa, $filtroTxt);
                break; // TCPDF ya envía el PDF
            }
            default:
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['ok'=>false,'error'=>'Acción no soportada']);
        }
    } catch (Throwable $e) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    }
}