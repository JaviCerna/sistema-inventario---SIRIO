<?php
// src/ProductController.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/Database.php';

function respond($data=null){ echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

try { $pdo = Database::getInstance()->getConnection(); }
catch(Throwable $e){ fail('DB: '.$e->getMessage(), 500); }

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
  switch ($action) {

    /* ================= OPCIONES ================= */
    case 'opciones': {
      $categorias     = $pdo->query("SELECT id_categoria, nombre FROM categorias WHERE activa=1 ORDER BY nombre")->fetchAll();
      $subcategorias  = $pdo->query("SELECT id_subcategoria, id_categoria, nombre FROM subcategorias WHERE activa=1 ORDER BY nombre")->fetchAll();
      $tallas         = $pdo->query("SELECT id_talla, codigo, nombre FROM tallas ORDER BY nombre")->fetchAll();
      $colores        = $pdo->query("SELECT id_color, nombre, hex FROM colores ORDER BY nombre")->fetchAll();
      $bodegas        = $pdo->query("SELECT id_bodega, nombre FROM bodegas WHERE activa=1 ORDER BY nombre")->fetchAll();
      respond(compact('categorias','subcategorias','tallas','colores','bodegas'));
    }

    /* ================= LISTADO ================= */
    case 'list': {
      $q = trim($_GET['q'] ?? '');
      $sql = "SELECT p.id_producto,p.sku,p.nombre,p.descripcion,p.costo_base,p.activo,p.creado_en,p.actualizado_en,
                     p.unidades,
                     c.nombre AS categoria, s.nombre AS subcategoria,
                     t.nombre AS talla, col.nombre AS color
              FROM productos p
              JOIN categorias c   ON c.id_categoria=p.id_categoria
              JOIN subcategorias s ON s.id_subcategoria=p.id_subcategoria
              LEFT JOIN tallas t  ON t.id_talla=p.id_talla
              LEFT JOIN colores col ON col.id_color=p.id_color";
      $params = [];
      if($q !== ''){
        $sql .= " WHERE p.sku LIKE ? OR p.nombre LIKE ?";
        $like = "%$q%"; $params = [$like, $like];
      }
      $sql .= " ORDER BY p.id_producto DESC";
      $stmt = $pdo->prepare($sql); $stmt->execute($params);
      $rows = $stmt->fetchAll();

      // Existencias por bodega
      if ($rows) {
        $ids = implode(',', array_map('intval', array_column($rows,'id_producto')));
        $ex = $pdo->query("SELECT e.id_producto, e.cantidad, b.nombre AS bodega
                           FROM existencias e JOIN bodegas b ON b.id_bodega=e.id_bodega
                           WHERE e.id_producto IN ($ids)")->fetchAll();
        $grp = [];
        foreach($ex as $r){ $grp[$r['id_producto']][] = ['bodega'=>$r['bodega'], 'cantidad'=>$r['cantidad']]; }
        foreach($rows as &$r){ $r['existencias'] = $grp[$r['id_producto']] ?? []; }
      }
      respond($rows);
    }

    /* ================= FIND ================= */
    case 'find': {
      $id = (int)($_GET['id'] ?? 0);
      if(!$id) fail('ID inválido');
      $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto=?");
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      if(!$row) fail('No encontrado', 404);
      respond($row);
    }

    /* ================= CREATE ================= */
    case 'create': {
      $sku  = trim($_POST['sku'] ?? '');
      $nom  = trim($_POST['nombre'] ?? '');
      $idc  = (int)($_POST['id_categoria'] ?? 0);
      $ids  = (int)($_POST['id_subcategoria'] ?? 0);
      $idt  = $_POST['id_talla'] !== '' ? (int)$_POST['id_talla'] : null;
      $idcol= $_POST['id_color'] !== '' ? (int)$_POST['id_color'] : null;
      $desc = trim($_POST['descripcion'] ?? '');
      $costo= $_POST['costo_base'] !== '' ? (float)$_POST['costo_base'] : null;
      $uni  = (int)($_POST['unidades'] ?? 0);
      $act  = (int)($_POST['activo'] ?? 1);
      if($sku==='' || $nom==='' || !$idc || !$ids) fail('Campos requeridos faltantes');
      $stmt = $pdo->prepare("INSERT INTO productos (sku,nombre,descripcion,costo_base,activo,id_categoria,id_subcategoria,id_talla,id_color,unidades)
                             VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stmt->execute([$sku,$nom,$desc?:null,$costo,$act,$idc,$ids,$idt,$idcol,$uni]);
      respond(['id_producto'=>$pdo->lastInsertId()]);
    }

    /* ================= UPDATE ================= */
    case 'update': {
      $id   = (int)($_POST['id_producto'] ?? 0);
      if(!$id) fail('ID inválido');
      $sku  = trim($_POST['sku'] ?? '');
      $nom  = trim($_POST['nombre'] ?? '');
      $idc  = (int)($_POST['id_categoria'] ?? 0);
      $ids  = (int)($_POST['id_subcategoria'] ?? 0);
      $idt  = $_POST['id_talla'] !== '' ? (int)$_POST['id_talla'] : null;
      $idcol= $_POST['id_color'] !== '' ? (int)$_POST['id_color'] : null;
      $desc = trim($_POST['descripcion'] ?? '');
      $costo= $_POST['costo_base'] !== '' ? (float)$_POST['costo_base'] : null;
      $uni  = (int)($_POST['unidades'] ?? 0);
      $act  = (int)($_POST['activo'] ?? 1);
      $stmt = $pdo->prepare("UPDATE productos SET sku=?,nombre=?,descripcion=?,costo_base=?,activo=?,
                             id_categoria=?,id_subcategoria=?,id_talla=?,id_color=?,unidades=? WHERE id_producto=?");
      $stmt->execute([$sku,$nom,$desc?:null,$costo,$act,$idc,$ids,$idt,$idcol,$uni,$id]);
      respond(true);
    }

    /* ================= DELETE ================= */
    case 'delete': {
      $id = (int)($_POST['id_producto'] ?? 0);
      if(!$id) fail('ID inválido');
      $pdo->prepare("DELETE FROM productos WHERE id_producto=?")->execute([$id]);
      respond(true);
    }

    /* ================= MOV ================= */
    case 'mov': {
      $id_bodega = (int)($_POST['id_bodega'] ?? 0);
      $id_prod   = (int)($_POST['id_producto'] ?? 0);
      $tipo      = $_POST['tipo'] ?? 'IN';
      $cantidad  = (float)($_POST['cantidad'] ?? 0);
      $costo     = $_POST['costo_unitario'] !== '' ? (float)$_POST['costo_unitario'] : null;
      $motivo    = trim($_POST['motivo'] ?? '');
      $fecha     = $_POST['fecha_evento'] ?? '';
      if(!$id_bodega || !$id_prod || $cantidad<=0 || !$fecha) fail('Datos de movimiento inválidos');
      $stmt = $pdo->prepare("INSERT INTO movimientos_inventario (id_bodega,id_producto,tipo,cantidad,costo_unitario,motivo,fecha_evento)
                             VALUES (?,?,?,?,?,?,?)");
      $stmt->execute([$id_bodega,$id_prod,$tipo,$cantidad,$costo,$motivo?:null,$fecha]);
      respond(true);
    }

    /* ================= TALLAS ================= */
    case 'talla_list':   { respond($pdo->query("SELECT id_talla,codigo,nombre FROM tallas ORDER BY nombre")->fetchAll()); }
    case 'talla_create': {
      $codigo = trim($_POST['codigo'] ?? ''); $nombre = trim($_POST['nombre'] ?? '');
      if($codigo==='' || $nombre==='') fail('Código y nombre son requeridos');
      $stmt=$pdo->prepare("INSERT INTO tallas (codigo,nombre) VALUES (?,?)"); $stmt->execute([$codigo,$nombre]);
      respond(['id_talla'=>$pdo->lastInsertId()]);
    }
    case 'talla_update': {
      $id = (int)($_POST['id_talla'] ?? 0); if(!$id) fail('ID inválido');
      $codigo = trim($_POST['codigo'] ?? ''); $nombre = trim($_POST['nombre'] ?? '');
      $stmt=$pdo->prepare("UPDATE tallas SET codigo=?,nombre=? WHERE id_talla=?"); $stmt->execute([$codigo,$nombre,$id]);
      respond(true);
    }
    case 'talla_delete': {
      $id = (int)($_POST['id_talla'] ?? 0); if(!$id) fail('ID inválido');
      $pdo->prepare("DELETE FROM tallas WHERE id_talla=?")->execute([$id]); respond(true);
    }

    /* ================= COLORES ================= */
    case 'color_list':   { respond($pdo->query("SELECT id_color,nombre,hex FROM colores ORDER BY nombre")->fetchAll()); }
    case 'color_create': {
      $nombre = trim($_POST['nombre'] ?? ''); $hex = trim($_POST['hex'] ?? '');
      if($nombre==='') fail('Nombre requerido');
      if($hex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/',$hex)) fail('HEX inválido');
      $stmt=$pdo->prepare("INSERT INTO colores (nombre,hex) VALUES (?,?)"); $stmt->execute([$nombre,$hex?:null]);
      respond(['id_color'=>$pdo->lastInsertId()]);
    }
    case 'color_update': {
      $id = (int)($_POST['id_color'] ?? 0); if(!$id) fail('ID inválido');
      $nombre = trim($_POST['nombre'] ?? ''); $hex = trim($_POST['hex'] ?? '');
      if($hex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/',$hex)) fail('HEX inválido');
      $stmt=$pdo->prepare("UPDATE colores SET nombre=?,hex=? WHERE id_color=?"); $stmt->execute([$nombre,$hex?:null,$id]);
      respond(true);
    }
    case 'color_delete': {
      $id = (int)($_POST['id_color'] ?? 0); if(!$id) fail('ID inválido');
      $pdo->prepare("DELETE FROM colores WHERE id_color=?")->execute([$id]); respond(true);
    }

    default: fail('Acción no soportada', 404);
  }
} catch (Throwable $e) {
  fail($e->getMessage(), 500);
}
