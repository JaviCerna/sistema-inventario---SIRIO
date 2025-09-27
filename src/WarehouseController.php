<?php
// src/WarehouseController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class WarehouseController {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Listado con búsqueda opcional por código, nombre, ciudad o país */
    public function list(string $q = ''): array {
        $sql = "SELECT id_bodega, codigo, nombre, direccion1, ciudad, pais, activa, creado_en, actualizado_en
                FROM bodegas";
        $params = [];
        if ($q !== '') {
            $sql .= " WHERE codigo LIKE :q
                      OR nombre LIKE :q
                      OR ciudad LIKE :q
                      OR pais LIKE :q";
            $params[':q'] = "%$q%";
        }
        $sql .= " ORDER BY creado_en DESC";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function find(int $id): array {
        $st = $this->db->prepare("SELECT * FROM bodegas WHERE id_bodega = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        if (!$row) throw new RuntimeException('Bodega no encontrada');
        return $row;
    }

    public function create(string $codigo, string $nombre, ?string $direccion1, ?string $ciudad, ?string $pais, int $activa): int {
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
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $codigo, string $nombre, ?string $direccion1, ?string $ciudad, ?string $pais, int $activa): bool {
        $this->find($id);
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
        return $st->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':direccion1' => $direccion1,
            ':ciudad' => $ciudad,
            ':pais' => $pais,
            ':activa' => $activa ? 1 : 0,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $this->find($id);
        $st = $this->db->prepare("DELETE FROM bodegas WHERE id_bodega = :id");
        return $st->execute([':id' => $id]);
    }
}

/* --- Router JSON para fetch() --- */
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $ctrl = new WarehouseController();
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list': {
                $q = trim((string)($_GET['q'] ?? ''));
                echo json_encode(['ok'=>true,'data'=>$ctrl->list($q)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'find': {
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['ok'=>true,'data'=>$ctrl->find($id)], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'create': {
                $codigo = (string)($_POST['codigo'] ?? '');
                $nombre = (string)($_POST['nombre'] ?? '');
                $direccion1 = isset($_POST['direccion1']) ? (string)$_POST['direccion1'] : null;
                $ciudad = isset($_POST['ciudad']) ? (string)$_POST['ciudad'] : null;
                $pais = isset($_POST['pais']) ? (string)$_POST['pais'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $id = $ctrl->create($codigo,$nombre,$direccion1,$ciudad,$pais,$activa);
                echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'update': {
                $id = (int)($_POST['id_bodega'] ?? 0);
                $codigo = (string)($_POST['codigo'] ?? '');
                $nombre = (string)($_POST['nombre'] ?? '');
                $direccion1 = isset($_POST['direccion1']) ? (string)$_POST['direccion1'] : null;
                $ciudad = isset($_POST['ciudad']) ? (string)$_POST['ciudad'] : null;
                $pais = isset($_POST['pais']) ? (string)$_POST['pais'] : null;
                $activa = (int)($_POST['activa'] ?? 1);
                $ctrl->update($id,$codigo,$nombre,$direccion1,$ciudad,$pais,$activa);
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                break;
            }
            case 'delete': {
                $id = (int)($_POST['id_bodega'] ?? 0);
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
