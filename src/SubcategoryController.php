<?php
// src/SubcategoryController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class SubcategoryController {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Listado con nombre de categoría (join) + búsqueda opcional */
    public function list(string $q = '', ?int $cat = null): array {
        $sql = "SELECT s.id_subcategoria, s.id_categoria, c.nombre AS categoria,
                       s.nombre, s.descripcion, s.activa, s.creada_en, s.actualizada_en
                FROM subcategorias s
                INNER JOIN categorias c ON c.id_categoria = s.id_categoria";
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
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY s.creada_en DESC";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Para llenar el combo de categorías */
    public function categories(): array {
        $st = $this->db->query("SELECT id_categoria, nombre FROM categorias WHERE activa = 1 ORDER BY nombre");
        return $st->fetchAll();
    }

    public function find(int $id): array {
        $st = $this->db->prepare("SELECT * FROM subcategorias WHERE id_subcategoria = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        if (!$row) throw new RuntimeException('Subcategoría no encontrada');
        return $row;
    }

    public function create(int $id_categoria, string $nombre, ?string $descripcion, int $activa): int {
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
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $id_categoria, string $nombre, ?string $descripcion, int $activa): bool {
        $this->find($id);
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
        return $st->execute([
            ':id_categoria' => $id_categoria,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $this->find($id);
        $st = $this->db->prepare("DELETE FROM subcategorias WHERE id_subcategoria = :id");
        return $st->execute([':id' => $id]);
    }
}

/* --- Router JSON para fetch() --- */
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $ctrl = new SubcategoryController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                $q = trim((string)($_GET['q'] ?? ''));
                $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
                echo json_encode(['ok'=>true,'data'=>$ctrl->list($q, $cat)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'categories': {
                echo json_encode(['ok'=>true,'data'=>$ctrl->categories()], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'find': {
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['ok'=>true,'data'=>$ctrl->find($id)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'create': {
                $id_categoria = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($id_categoria,$nombre,$descripcion,$activa);
                echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                $id = (int)($_POST['id_subcategoria'] ?? 0);
                $id_categoria = (int)($_POST['id_categoria'] ?? 0);
                $nombre = (string)($_POST['nombre'] ?? '');
                $descripcion = isset($_POST['descripcion']) ? (string)$_POST['descripcion'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id,$id_categoria,$nombre,$descripcion,$activa);
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                $id = (int)($_POST['id_subcategoria'] ?? 0);
                $ctrl->delete($id);
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            default:
                http_response_code(400);
                echo json_encode(['ok'=>false,'error'=>'Acción no soportada']);
        }
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    }
}
