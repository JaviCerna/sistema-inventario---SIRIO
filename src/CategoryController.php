<?php
// src/CategoryController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auditor.php';
require_once __DIR__ . '/../vendor/autoload.php';      // Composer (TCPDF)
require_once __DIR__ . '/Reports/CategoryPdf.php';     // Clase del reporte

class CategoryController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** * Listado con búsqueda y paginación (MODIFICADO)
     * @param string $q El término de búsqueda.
     * @param int $page La página actual (base 1).
     * @param int $limit El número de registros por página.
     * @return array ['total_records' => int, 'data' => array]
     */
    public function list(string $q = '', int $page = 1, int $limit = 10): array {
        // 1. Consulta base para contar registros y para obtener datos
        $baseSql = "FROM categorias";
        $whereSql = "";
        $params = [];
        
        // Agregar cláusula WHERE si hay búsqueda
        if ($q !== '') {
            $whereSql = " WHERE nombre LIKE :q OR descripcion LIKE :q2";
            $params[':q']  = "%$q%";
            $params[':q2'] = "%$q%";
        }
        
        // 2. Obtener el total de registros (sin LIMIT/OFFSET)
        $stCount = $this->db->prepare("SELECT COUNT(*) " . $baseSql . $whereSql);
        $stCount->execute($params);
        $totalRecords = (int)$stCount->fetchColumn();

        // 3. Preparar la consulta para los datos de la página
        $offset = ($page - 1) * $limit;
        
        $dataSql = "SELECT id_categoria, nombre, descripcion, activa, creada_en, actualizada_en "
                 . $baseSql
                 . $whereSql
                 . " ORDER BY creada_en DESC"
                 . " LIMIT :limit OFFSET :offset";

        $st = $this->db->prepare($dataSql);
        
        // Ejecutar con todos los parámetros, incluyendo LIMIT/OFFSET
        
        // Necesitamos asegurar que LIMIT y OFFSET se tratan como INT
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        // Bind los parámetros de búsqueda de forma normal
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, PDO::PARAM_STR);
        }

        $st->execute();

        return [
            'total_records' => $totalRecords,
            'data' => $st->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function find(int $id): array {
        $st = $this->db->prepare("SELECT * FROM categorias WHERE id_categoria = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) { throw new RuntimeException('Categoría no encontrada'); }
        return $row;
    }

    // AÑADIDO: $userId
    public function create(string $nombre, ?string $descripcion, int $activa, ?int $userId): int {
        if (trim($nombre) === '') { throw new InvalidArgumentException('El nombre es obligatorio.'); }
        
        $st = $this->db->prepare(
            "INSERT INTO categorias (nombre, descripcion, activa)
             VALUES (:nombre, :descripcion, :activa)"
        );
        $st->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0
        ]);
        $id = (int)$this->db->lastInsertId();

        // AUDITORÍA
        Auditor::log($userId, 'categorias', $id, 'CREATE', [
            'nombre' => $nombre, 
            'descripcion' => $descripcion, 
            'activa' => $activa
        ]);
        
        return $id;
    }

    // AÑADIDO: $userId
    public function update(int $id, string $nombre, ?string $descripcion, int $activa, ?int $userId): bool {
        $old_data = $this->find($id); // Obtener datos antes de actualizar
        if (trim($nombre) === '') { throw new InvalidArgumentException('El nombre es obligatorio.'); }
        
        $st = $this->db->prepare(
            "UPDATE categorias
               SET nombre = :nombre,
                   descripcion = :descripcion,
                   activa = :activa
             WHERE id_categoria = :id"
        );
        $success = $st->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
        
        if ($success) {
            // AUDITORÍA: Comparar y registrar solo los cambios
            $new_data = ['nombre' => $nombre, 'descripcion' => $descripcion, 'activa' => $activa];
            $changes = [];
            foreach ($new_data as $key => $value) {
                // Hay que manejar la diferencia de tipos entre DB (string/int) y PHP (int/string)
                $old_val = $old_data[$key];
                $new_val = ($key === 'activa') ? ($value ? 1 : 0) : $value;
                
                if ((string)$old_val !== (string)$new_val) {
                    $changes[$key] = ['old' => $old_data[$key], 'new' => $new_val];
                }
            }

            if (!empty($changes)) {
                 Auditor::log($userId, 'categorias', $id, 'UPDATE', $changes);
            }
        }
        
        return $success;
    }

    /**
     * @param int $id ID de la categoría a eliminar.
     * @param ?int $userId ID del usuario que realiza la acción.
     * @return bool
     * @throws RuntimeException Si la categoría está relacionada con subcategorías. 
     */
    public function delete(int $id, ?int $userId): bool {
        // 1. VERIFICACIÓN DE INTEGRIDAD REFERENCIAL
        $stCheck = $this->db->prepare("SELECT COUNT(*) FROM subcategorias WHERE id_categoria = :id");
        $stCheck->execute([':id' => $id]);
        if ((int)$stCheck->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar la categoría porque ya tiene subcategorías relacionadas.');
        }

        // 2. Proceso de eliminación
        $data = $this->find($id); // Obtener datos antes de eliminar
        $st = $this->db->prepare("DELETE FROM categorias WHERE id_categoria = :id");
        $success = $st->execute([':id' => $id]);
        
        if ($success) {
            // AUDITORÍA
            Auditor::log($userId, 'categorias', $id, 'DELETE', [
                'registro_eliminado' => $data['nombre'] ?? 'ID ' . $id
            ]);
        }
        
        return $success;
    }
}

/* --- Router JSON/PDF (MODIFICADO para paginación) --- */
if (php_sapi_name() !== 'cli') {
    // IMPORTANTE: Requiere Auth para obtener el ID del usuario
    require_once __DIR__ . '/Auth.php';
    $auth = new Auth();
    $user = $auth->check() ? $auth->user() : null;
    $userId = $user['id'] ?? null; // ID del usuario logueado

    try {
        $ctrl   = new CategoryController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                header('Content-Type: application/json; charset=utf-8');
                $q     = trim((string)($_GET['q'] ?? ''));
                // NUEVO: Obtener parámetros de paginación
                $page  = (int)($_GET['page'] ?? 1);
                $limit = (int)($_GET['limit'] ?? 10);
                
                // Asegurar valores mínimos
                $page = max(1, $page);
                $limit = max(1, $limit); 
                
                // Llamada al método list con paginación
                $result = $ctrl->list($q, $page, $limit);
                
                echo json_encode(['ok' => true, 'data' => $result['data'], 'total_records' => $result['total_records']], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'find': {
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['ok' => true, 'data' => $ctrl->find($id)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'create': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($nombre, $descripcion, $activa, $userId); // <--- NUEVO: $userId
                echo json_encode(['ok' => true, 'id' => $id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id, $nombre, $descripcion, $activa, $userId); // <--- NUEVO: $userId
                echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_categoria'] ?? 0);
                $ctrl->delete($id, $userId); // Si falla, lanza RuntimeException
                echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'export_pdf': {
                // ¡Importante! Nada de JSON aquí
                if (ob_get_length()) { ob_end_clean(); }

                $q = trim((string)($_GET['q'] ?? ''));
                // Obtener todos los registros filtrados para el PDF
                $rows = $ctrl->list($q, 1, 999999)['data']; 

                $empresa = 'SIRIO S.A. DE C.V.';
                $report  = new CategoryPdf('Categorías - Reporte');
                $report->output($rows, $empresa, $q !== '' ? $q : null);
                break;
            }
            default:
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
        }
    } catch (Throwable $e) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
}