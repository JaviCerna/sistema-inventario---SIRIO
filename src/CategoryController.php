<?php
// src/CategoryController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class CategoryController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function list(string $q = ''): array {
        $sql = "SELECT id_categoria, nombre, descripcion, activa, creada_en, actualizada_en
                FROM categorias";
        $params = [];
        if ($q !== '') {
            $sql .= " WHERE nombre LIKE :q OR descripcion LIKE :q2";
            $params[':q']  = "%$q%";
            $params[':q2'] = "%$q%";
        }
        $sql .= " ORDER BY creada_en DESC";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function find(int $id): array {
        $st = $this->db->prepare("SELECT * FROM categorias WHERE id_categoria = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        if (!$row) { throw new RuntimeException('Categoría no encontrada'); }
        return $row;
    }

    public function create(string $nombre, ?string $descripcion, int $activa): int {
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
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $nombre, ?string $descripcion, int $activa): bool {
        $this->find($id); // valida existencia
        if (trim($nombre) === '') { throw new InvalidArgumentException('El nombre es obligatorio.'); }
        $st = $this->db->prepare(
            "UPDATE categorias
               SET nombre = :nombre,
                   descripcion = :descripcion,
                   activa = :activa
             WHERE id_categoria = :id"
        );
        return $st->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $this->find($id); // valida existencia
        $st = $this->db->prepare("DELETE FROM categorias WHERE id_categoria = :id");
        return $st->execute([':id' => $id]);
    }
}

/* --- Router simple: responde JSON a fetch() --- */
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $ctrl   = new CategoryController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                $q = trim((string)($_GET['q'] ?? ''));
                $data = $ctrl->list($q);
                echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'find': {
                $id = (int)($_GET['id'] ?? 0);
                $row = $ctrl->find($id);
                echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'create': {
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($nombre, $descripcion, $activa);
                echo json_encode(['ok' => true, 'id' => $id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                $id = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id, $nombre, $descripcion, $activa);
                echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                $id = (int)($_POST['id_categoria'] ?? 0);
                $ctrl->delete($id);
                echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
                break;
            }
            default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
        }
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
}
