<?php
// src/WarehouseController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auditor.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Composer
require_once __DIR__ . '/Reports/WarehousePdf.php';

class WarehouseController {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Listado con búsqueda opcional y Paginación (MODIFICADO)
     * @param string $q El término de búsqueda.
     * @param int $page La página actual (base 1).
     * @param int $limit El número de registros por página.
     * @return array ['total_records' => int, 'data' => array]
     */
    public function list(string $q = '', int $page = 1, int $limit = 10): array {
        // 1. Consulta base y cláusula WHERE
        $baseSql = "FROM bodegas";
        $params = [];
        $whereSql = "";
        
        if ($q !== '') {
            $whereSql = " WHERE codigo LIKE :q
                      OR nombre LIKE :q2
                      OR ciudad LIKE :q3
                      OR pais LIKE :q4";
            $params[':q'] = "%$q%";
            $params[':q2'] = "%$q%";
            $params[':q3'] = "%$q%";
            $params[':q4'] = "%$q%";
        }
        
        // 2. Obtener el total de registros
        $stCount = $this->db->prepare("SELECT COUNT(*) " . $baseSql . $whereSql);
        $stCount->execute($params);
        $totalRecords = (int)$stCount->fetchColumn();

        // 3. Preparar la consulta para los datos de la página
        $offset = ($page - 1) * $limit;
        
        $dataSql = "SELECT id_bodega, codigo, nombre, direccion1, ciudad, pais, activa, creado_en, actualizado_en "
                 . $baseSql
                 . $whereSql
                 . " ORDER BY creado_en DESC"
                 . " LIMIT :limit OFFSET :offset";

        $st = $this->db->prepare($dataSql);
        
        // Asignar parámetros para la consulta de datos
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
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
        $st = $this->db->prepare("SELECT * FROM bodegas WHERE id_bodega = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Bodega no encontrada');
        return $row;
    }

    // AÑADIDO: $userId
    public function create(string $codigo, string $nombre, ?string $direccion1, ?string $ciudad, ?string $pais, int $activa, ?int $userId): int {
        if (trim($codigo) === '' || trim($nombre) === '') {
            throw new InvalidArgumentException('Código y nombre son obligatorios.');
        }
        $st = $this->db->prepare(
            "INSERT INTO bodegas (codigo, nombre, direccion1, ciudad, pais, activa)
             VALUES (:codigo, :nombre, :direccion1, :ciudad, :pais, :activa)"
        );
        $st->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':direccion1' => $direccion1,
            ':ciudad' => $ciudad,
            ':pais' => $pais,
            ':activa' => $activa ? 1 : 0
        ]);
        $id = (int)$this->db->lastInsertId();

        // AUDITORÍA
        Auditor::log($userId, 'bodegas', $id, 'CREATE', [
            'codigo' => $codigo, 
            'nombre' => $nombre, 
            'ciudad' => $ciudad,
            'pais' => $pais,
            'activa' => $activa
        ]);

        return $id;
    }

    // AÑADIDO: $userId
    public function update(int $id, string $codigo, string $nombre, ?string $direccion1, ?string $ciudad, ?string $pais, int $activa, ?int $userId): bool {
        $old_data = $this->find($id); // Obtener datos antes de actualizar
        if (trim($codigo) === '' || trim($nombre) === '') {
            throw new InvalidArgumentException('Código y nombre son obligatorios.');
        }
        $st = $this->db->prepare(
            "UPDATE bodegas
               SET codigo = :codigo,
                   nombre = :nombre,
                   direccion1 = :direccion1,
                   ciudad = :ciudad,
                   pais = :pais,
                   activa = :activa
             WHERE id_bodega = :id"
        );
        $success = $st->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':direccion1' => $direccion1,
            ':ciudad' => $ciudad,
            ':pais' => $pais,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
        
        if ($success) {
            // AUDITORÍA: Comparar y registrar solo los cambios
            $new_data = [
                'codigo' => $codigo, 
                'nombre' => $nombre, 
                'direccion1' => $direccion1, 
                'ciudad' => $ciudad, 
                'pais' => $pais, 
                'activa' => $activa
            ];
            $changes = [];
            foreach ($new_data as $key => $value) {
                $old_val = $old_data[$key];
                $new_val = ($key === 'activa') ? ($value ? 1 : 0) : $value;
                
                // Comparación flexible (maneja nulos, int y string)
                if ((string)$old_val !== (string)$new_val) {
                    $changes[$key] = ['old' => $old_data[$key], 'new' => $new_val];
                }
            }

            if (!empty($changes)) {
                 Auditor::log($userId, 'bodegas', $id, 'UPDATE', $changes);
            }
        }

        return $success;
    }

    /**
     * Elimina una bodega, verificando que no esté relacionada con existencias (MODIFICADO).
     * @param int $id ID de la bodega a eliminar.
     * @param ?int $userId ID del usuario que realiza la acción.
     * @return bool
     * @throws RuntimeException Si la bodega está vinculada a existencias.
     */
    public function delete(int $id, ?int $userId): bool {
        // 1. VERIFICACIÓN DE INTEGRIDAD REFERENCIAL (NUEVO)
        $stCheck = $this->db->prepare("SELECT COUNT(*) FROM existencias WHERE id_bodega = :id");
        $stCheck->execute([':id' => $id]);
        if ((int)$stCheck->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar la bodega porque ya está vinculada a existencias o movimientos de productos.');
        }
        
        $data = $this->find($id); // Obtener datos antes de eliminar
        $st = $this->db->prepare("DELETE FROM bodegas WHERE id_bodega = :id");
        $success = $st->execute([':id' => $id]);
        
        if ($success) {
            // AUDITORÍA
            Auditor::log($userId, 'bodegas', $id, 'DELETE', [
                'registro_eliminado' => $data['nombre'] ?? 'ID ' . $id,
                'codigo' => $data['codigo']
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
        $ctrl = new WarehouseController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                header('Content-Type: application/json; charset=utf-8');
                $q = trim((string)($_GET['q'] ?? ''));
                // NUEVO: Paginación
                $page  = (int)($_GET['page'] ?? 1);
                $limit = (int)($_GET['limit'] ?? 10);
                $page = max(1, $page);
                $limit = max(1, $limit); 
                
                $result = $ctrl->list($q, $page, $limit);

                echo json_encode(['ok'=>true, 'data'=>$result['data'], 'total_records' => $result['total_records']], JSON_UNESCAPED_UNICODE);
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
                $codigo = (string)($_POST['codigo'] ?? '');
                $nombre = (string)($_POST['nombre'] ?? '');
                $direccion1 = isset($_POST['direccion1']) ? (string)$_POST['direccion1'] : null;
                $ciudad = isset($_POST['ciudad']) ? (string)$_POST['ciudad'] : null;
                $pais = isset($_POST['pais']) ? (string)$_POST['pais'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($codigo,$nombre,$direccion1,$ciudad,$pais,$activa, $userId);
                echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_bodega'] ?? 0);
                $codigo = (string)($_POST['codigo'] ?? '');
                $nombre = (string)($_POST['nombre'] ?? '');
                $direccion1 = isset($_POST['direccion1']) ? (string)$_POST['direccion1'] : null;
                $ciudad = isset($_POST['ciudad']) ? (string)$_POST['ciudad'] : null;
                $pais = isset($_POST['pais']) ? (string)$_POST['pais'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id,$codigo,$nombre,$direccion1,$ciudad,$pais,$activa, $userId);
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                if (!$userId) throw new Exception('Usuario no autenticado para esta acción.'); // Seguridad
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id_bodega'] ?? 0);
                // Si la bodega tiene existencias, delete() lanzará una RuntimeException
                $ctrl->delete($id, $userId); 
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'export_pdf': {
                // No header JSON aquí; TCPDF maneja salida binaria
                if (ob_get_length()) { ob_end_clean(); }
                $q = trim((string)($_GET['q'] ?? ''));
                $rows = $ctrl->list($q, 1, 999999)['data']; // Obtener todos los registros filtrados

                // Definir la ruta del logo (Asegúrate que esta ruta sea correcta)
                $logoPath = __DIR__ . '/../../public/assets/sirio-logo.png';
                $logoPath = file_exists($logoPath) ? $logoPath : null;

                $empresa = 'SIRIO S.A. DE C.V.';
                $report = new WarehousePdf('Bodegas - Reporte');
                
                // Preparar el texto de filtro para el PDF
                $filtroTxt = $q !== '' ? 'Búsqueda: ' . $q : null;

                $report->output($rows, $empresa, $filtroTxt, $logoPath);
                break;
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