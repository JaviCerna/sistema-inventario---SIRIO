<?php
// src/ProductController.php
declare(strict_types=1);

// --- Bufferiza TODO para poder limpiar ruido (BOM/espacios/notices) antes de responder
ob_start();

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';    // Para obtener el ID del usuario
require_once __DIR__ . '/Auditor.php'; // Para registrar logs

/* ================== CARGA TCPDF (Composer o directa) ==================
   Intentamos primero con Composer autoload; si no existe, cargamos tcpdf.php
   directamente desde ubicaciones típicas. No crea archivos nuevos. */
(function () {
  if (!class_exists('TCPDF', false)) {
    $tryAutoload = [
      __DIR__ . '/../vendor/autoload.php',    // proyectoRoot/vendor/autoload.php (común)
      __DIR__ . '/../../vendor/autoload.php', // por si /src está dos niveles bajo
    ];
    foreach ($tryAutoload as $path) {
      if (is_file($path)) { require_once $path; break; }
    }
  }
  if (!class_exists('TCPDF', false)) {
    $tryTcpdf = [
      __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php',
      __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php',
      __DIR__ . '/tcpdf/tcpdf.php', // por si alguien dejó tcpdf en /src/tcpdf
    ];
    foreach ($tryTcpdf as $path) {
      if (is_file($path)) { require_once $path; break; }
    }
  }
  // Si aún no está, dejamos que falle al usar y mostraremos mensaje claro abajo.
})();

// Para PDFs (estas clases usan "use TCPDF;")
require_once __DIR__ . '/Reports/ProductPdf.php';
require_once __DIR__ . '/Reports/ProductDetailPdf.php';

// --- Helpers de respuesta JSON "a prueba de ruido"
function respond($data = null): void {
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
  exit;
}
function fail(string $msg, int $code = 400): void {
  while (ob_get_level() > 0) { ob_end_clean(); }
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

// --- Conexión
try {
  $pdo = Database::getInstance()->getConnection();
} catch (Throwable $e) {
  fail('DB: ' . $e->getMessage(), 500);
}

// --- Obtener ID del usuario logueado (Se usa en todas las acciones de modificación)
$auth = new Auth();
$user = $auth->check() ? $auth->user() : null;
$userId = $user['id'] ?? null;

// --- Funciones helper para auditoría
function get_product_data(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function get_talla_data(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM tallas WHERE id_talla=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function get_color_data(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM colores WHERE id_color=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
/** Compara dos arrays (old y new) y devuelve solo los cambios. */
function array_diff_assoc_recursive($array1, $array2): array {
    $difference = [];
    foreach ($array1 as $key => $value) {
        if (array_key_exists($key, $array2)) {
            $value2 = $array2[$key];
            // Si el valor es una cadena numérica o int, compara como string para manejar NULLs/float
            if ((string)$value !== (string)$value2) {
                // Excluir claves internas de la base de datos que no se editan (ej. timestamp)
                if (!in_array($key, ['creado_en', 'actualizado_en'])) {
                    $difference[$key] = ['old' => $value, 'new' => $value2];
                }
            }
        }
    }
    return $difference;
}


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

    /* ================= LISTADO (con Paginación) ================= */
    case 'list': {
      $q     = trim($_GET['q'] ?? '');
      $page  = (int)($_GET['page'] ?? 1);
      $limit = (int)($_GET['limit'] ?? 10);
      $offset = max(0, ($page - 1) * $limit);

      // --- Base de la consulta (sin LIMIT)
      $sql_base = "FROM productos p
                   JOIN categorias c    ON c.id_categoria=p.id_categoria
                   JOIN subcategorias s  ON s.id_subcategoria=p.id_subcategoria
                   LEFT JOIN tallas t    ON t.id_talla=p.id_talla
                   LEFT JOIN colores col ON col.id_color=p.id_color";
      
      $where_clause = "";
      $params = [];
      if ($q !== '') {
        $where_clause = " WHERE p.sku LIKE ? OR p.nombre LIKE ?";
        $like = "%$q%"; $params = [$like, $like];
      }

      // 1. Conteo total de filas
      $stmt_count = $pdo->prepare("SELECT COUNT(p.id_producto) " . $sql_base . $where_clause);
      $stmt_count->execute($params);
      $total = (int)$stmt_count->fetchColumn();

      // 2. Consulta de datos paginados
      $sql = "SELECT p.id_producto,p.sku,p.nombre,p.descripcion,p.costo_base,p.activo,p.creado_en,p.actualizado_en,
                     p.unidades,
                     c.nombre AS categoria, s.nombre AS subcategoria,
                     t.nombre AS talla, col.nombre AS color "
             . $sql_base . $where_clause
             . " ORDER BY p.id_producto DESC LIMIT $limit OFFSET $offset";
      
      $stmt = $pdo->prepare($sql);
      // Los parámetros deben ser re-enviados para la consulta principal
      $stmt->execute($params);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Existencias por bodega
      if ($rows && count($rows) > 0) {
        $ids = implode(',', array_map('intval', array_column($rows,'id_producto')));
        $ex  = $pdo->query("SELECT e.id_producto, e.cantidad, b.nombre AS bodega
                            FROM existencias e
                            JOIN bodegas b ON b.id_bodega=e.id_bodega
                            WHERE e.id_producto IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
        $grp = [];
        foreach($ex as $r){ $grp[$r['id_producto']][] = ['bodega'=>$r['bodega'], 'cantidad'=>$r['cantidad']]; }
        foreach($rows as &$r){ $r['existencias'] = $grp[$r['id_producto']] ?? []; }
        unset($r);
      }
      
      respond([
          'rows' => $rows, 
          'total' => $total, 
          'page' => $page, 
          'limit' => $limit
      ]);
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

    /* ================= CREATE (PRODUCTO) ================= */
    case 'create': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      
      $sku   = trim($_POST['sku'] ?? '');
      $nom   = trim($_POST['nombre'] ?? '');
      $idc   = (int)($_POST['id_categoria'] ?? 0);
      $ids   = (int)($_POST['id_subcategoria'] ?? 0);
      $idt   = ($_POST['id_talla'] ?? '') !== '' ? (int)$_POST['id_talla'] : null;
      $idcol = ($_POST['id_color'] ?? '') !== '' ? (int)$_POST['id_color'] : null;
      $desc  = trim($_POST['descripcion'] ?? '');
      $costo = ($_POST['costo_base'] ?? '') !== '' ? (float)$_POST['costo_base'] : null;
      $uni   = 0; // REQUERIDO: Las unidades inician en 0 y solo se actualizan por movimientos
      $act   = (int)($_POST['activo'] ?? 1);

      if($sku==='' || $nom==='' || !$idc || !$ids) fail('Campos requeridos faltantes');

      $stmt = $pdo->prepare("INSERT INTO productos (sku,nombre,descripcion,costo_base,activo,id_categoria,id_subcategoria,id_talla,id_color,unidades)
                             VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stmt->execute([$sku,$nom,$desc?:null,$costo,$act,$idc,$ids,$idt,$idcol,$uni]);
      $id_producto = (int)$pdo->lastInsertId();

      // AUDITORÍA
      Auditor::log($userId, 'productos', $id_producto, 'CREATE', [
          'sku' => $sku, 
          'nombre' => $nom, 
          'costo_base' => $costo, 
          'activo' => $act
      ]);

      respond(['id_producto'=>$id_producto]);
    }

    /* ================= UPDATE (PRODUCTO) ================= */
    case 'update': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id    = (int)($_POST['id_producto'] ?? 0);
      if(!$id) fail('ID inválido');
      $old_data = get_product_data($pdo, $id);
      if (!$old_data) fail('Producto no encontrado', 404);

      $sku   = trim($_POST['sku'] ?? '');
      $nom   = trim($_POST['nombre'] ?? '');
      $idc   = (int)($_POST['id_categoria'] ?? 0);
      $ids   = (int)($_POST['id_subcategoria'] ?? 0);
      $idt   = ($_POST['id_talla'] ?? '') !== '' ? (int)$_POST['id_talla'] : null;
      $idcol = ($_POST['id_color'] ?? '') !== '' ? (int)$_POST['id_color'] : null;
      $desc  = trim($_POST['descripcion'] ?? '');
      $costo = ($_POST['costo_base'] ?? '') !== '' ? (float)$_POST['costo_base'] : null;
      // $uni ya no se toma del POST, se mantiene el valor original o se recalcula si aplica
      $uni   = (int)($old_data['unidades'] ?? 0); // Mantener el valor actual de unidades
      $act   = (int)($_POST['activo'] ?? 1);

      $new_data = [
          'id_producto' => $id, 'sku' => $sku, 'nombre' => $nom, 'descripcion' => $desc?:null, 
          'costo_base' => (string)$costo, 'activo' => $act, 'id_categoria' => $idc, 'id_subcategoria' => $ids, 
          'id_talla' => $idt, 'id_color' => $idcol, 'unidades' => $uni // Unidades no se actualiza desde aquí
      ];
      
      $stmt = $pdo->prepare("UPDATE productos
                               SET sku=?,nombre=?,descripcion=?,costo_base=?,activo=?,
                                   id_categoria=?,id_subcategoria=?,id_talla=?,id_color=?,unidades=?
                             WHERE id_producto=?");
      $stmt->execute([$sku,$nom,$desc?:null,$costo,$act,$idc,$ids,$idt,$idcol,$uni,$id]);

      // AUDITORÍA: Comparar y registrar cambios
      $changes = array_diff_assoc_recursive($old_data, $new_data);
      // Quitar la clave 'unidades' de los cambios ya que se gestiona por movimientos
      unset($changes['unidades']); 
      if (!empty($changes)) {
          Auditor::log($userId, 'productos', $id, 'UPDATE', $changes);
      }
      
      respond(true);
    }

    /* ================= DELETE (PRODUCTO) ================= */
    case 'delete': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id = (int)($_POST['id_producto'] ?? 0);
      if(!$id) fail('ID inválido');
      $old_data = get_product_data($pdo, $id);
      if (!$old_data) fail('Producto no encontrado', 404);
      
      // NUEVA VALIDACIÓN: Verificar si el producto tiene movimientos
      $stmt_mov = $pdo->prepare("SELECT COUNT(*) FROM movimientos_inventario WHERE id_producto = ?");
      $stmt_mov->execute([$id]);
      $count_mov = (int)$stmt_mov->fetchColumn();
      
      if ($count_mov > 0) {
        // Lanza un error si hay movimientos asociados
        fail('No se puede eliminar el producto porque tiene ' . $count_mov . ' movimientos de inventario registrados.', 409); 
      }
      
      $pdo->prepare("DELETE FROM productos WHERE id_producto=?")->execute([$id]);
      $pdo->prepare("DELETE FROM existencias WHERE id_producto=?")->execute([$id]);

      // AUDITORÍA
      Auditor::log($userId, 'productos', $id, 'DELETE', [
          'registro_eliminado' => $old_data['nombre'] ?? 'ID ' . $id,
          'sku' => $old_data['sku']
      ]);
      
      respond(true);
    }

    /* ================= MOVIMIENTO INVENTARIO ================= */
    case 'mov': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      
      $id_bodega = (int)($_POST['id_bodega'] ?? 0);
      $id_prod   = (int)($_POST['id_producto'] ?? 0);
      $tipo      = $_POST['tipo'] ?? 'IN';
      $cantidad  = (float)($_POST['cantidad'] ?? 0);
      $costo     = ($_POST['costo_unitario'] ?? '') !== '' ? (float)$_POST['costo_unitario'] : null;
      $motivo    = trim($_POST['motivo'] ?? '');
      $fecha     = $_POST['fecha_evento'] ?? '';

      if(!$id_bodega || !$id_prod || $cantidad<=0 || !$fecha) fail('Datos de movimiento inválidos');

      // 1. REGISTRAR EL MOVIMIENTO
      $stmt = $pdo->prepare("INSERT INTO movimientos_inventario (id_bodega,id_producto,tipo,cantidad,costo_unitario,motivo,id_usuario,fecha_evento)
                             VALUES (?,?,?,?,?,?,?,?)");
      $stmt->execute([$id_bodega,$id_prod,$tipo,$cantidad,$costo,$motivo?:null,$userId,$fecha]);
      $id_movimiento = (int)$pdo->lastInsertId();

      // 2. ACTUALIZAR EXISTENCIAS POR BODEGA (TABLA existencias)

      // Determinar el factor de cambio (+cantidad para IN/AJUSTE, -cantidad para OUT)
      $factor = 0;
      if ($tipo === 'IN' || $tipo === 'AJUSTE') {
        $factor = $cantidad;
      } elseif ($tipo === 'OUT') {
        $factor = -$cantidad;
      }

      if ($factor !== 0) {
          // Obtener el saldo actual (COALESCE asegura que si no existe, el saldo es 0)
          $stmt_saldo = $pdo->prepare("
              SELECT COALESCE(cantidad, 0) 
              FROM existencias 
              WHERE id_bodega = ? AND id_producto = ?
          ");
          $stmt_saldo->execute([$id_bodega, $id_prod]);
          $saldo_actual = (float)$stmt_saldo->fetchColumn();

          // Calcular el nuevo saldo (SOLO se suma/resta el factor)
          $nuevo_saldo = $saldo_actual + $factor;

          // Aplicar la actualización/inserción (UPSERT)
          // Esto SOBRESCRIBE la cantidad con el $nuevo_saldo. Requiere clave única.
          $stmt_upsert = $pdo->prepare("
              INSERT INTO existencias (id_bodega, id_producto, cantidad)
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)
          ");
          $stmt_upsert->execute([$id_bodega, $id_prod, $nuevo_saldo]);
      }


      // 3. ACTUALIZAR EL TOTAL DE UNIDADES DEL PRODUCTO (TABLA productos)
      // Recalcular el total sumando todas las existencias para ese producto
      $total_unidades = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) FROM existencias WHERE id_producto=?");
      $total_unidades->execute([$id_prod]);
      $unidades = (float)$total_unidades->fetchColumn();

      $pdo->prepare("UPDATE productos SET unidades=? WHERE id_producto=?")
          ->execute([$unidades, $id_prod]);
      
      // AUDITORÍA
      Auditor::log($userId, 'movimientos_inventario', $id_movimiento, $tipo === 'IN' ? 'CREATE' : ($tipo === 'OUT' ? 'DELETE' : 'UPDATE'), [
          'id_bodega' => $id_bodega, 
          'id_producto' => $id_prod, 
          'tipo' => $tipo, 
          'cantidad' => $cantidad
      ]);
      
      respond(true);
    }

    /* ================= TALLAS ================= */
    case 'talla_list':   {
      $rows = $pdo->query("SELECT id_talla,codigo,nombre FROM tallas ORDER BY nombre")->fetchAll();
      respond($rows);
    }
    case 'talla_create': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $codigo = trim($_POST['codigo'] ?? ''); $nombre = trim($_POST['nombre'] ?? '');
      if($codigo==='' || $nombre==='') fail('Código y nombre son requeridos');
      $stmt=$pdo->prepare("INSERT INTO tallas (codigo,nombre) VALUES (?,?)"); $stmt->execute([$codigo,$nombre]);
      $id = (int)$pdo->lastInsertId();
      Auditor::log($userId, 'tallas', $id, 'CREATE', ['codigo' => $codigo, 'nombre' => $nombre]); // AUDITORÍA
      respond(['id_talla'=>$id]);
    }
    case 'talla_update': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id = (int)($_POST['id_talla'] ?? 0); if(!$id) fail('ID inválido');
      $old_data = get_talla_data($pdo, $id);
      $codigo = trim($_POST['codigo'] ?? ''); $nombre = trim($_POST['nombre'] ?? '');
      $stmt=$pdo->prepare("UPDATE tallas SET codigo=?,nombre=? WHERE id_talla=?"); $stmt->execute([$codigo,$nombre,$id]);
      
      $changes = array_diff_assoc_recursive($old_data, ['codigo' => $codigo, 'nombre' => $nombre, 'id_talla' => $id]);
      if (!empty($changes)) { Auditor::log($userId, 'tallas', $id, 'UPDATE', $changes); } // AUDITORÍA
      
      respond(true);
    }
    case 'talla_delete': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id = (int)($_POST['id_talla'] ?? 0); if(!$id) fail('ID inválido');
      $old_data = get_talla_data($pdo, $id);
      $pdo->prepare("DELETE FROM tallas WHERE id_talla=?")->execute([$id]); 
      Auditor::log($userId, 'tallas', $id, 'DELETE', ['registro_eliminado' => $old_data['nombre'] ?? 'ID ' . $id]); // AUDITORÍA
      respond(true);
    }

    /* ================= COLORES ================= */
    case 'color_list':   {
      $rows = $pdo->query("SELECT id_color,nombre,hex FROM colores ORDER BY nombre")->fetchAll();
      respond($rows);
    }
    case 'color_create': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $nombre = trim($_POST['nombre'] ?? ''); $hex = trim($_POST['hex'] ?? '');
      if($nombre==='') fail('Nombre requerido');
      if($hex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/',$hex)) fail('HEX inválido');
      $stmt=$pdo->prepare("INSERT INTO colores (nombre,hex) VALUES (?,?)"); $stmt->execute([$nombre,$hex?:null]);
      $id = (int)$pdo->lastInsertId();
      Auditor::log($userId, 'colores', $id, 'CREATE', ['nombre' => $nombre, 'hex' => $hex?:'']); // AUDITORÍA
      respond(['id_color'=>$id]);
    }
    case 'color_update': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id = (int)($_POST['id_color'] ?? 0); if(!$id) fail('ID inválido');
      $old_data = get_color_data($pdo, $id);
      $nombre = trim($_POST['nombre'] ?? ''); $hex = trim($_POST['hex'] ?? '');
      if($hex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/',$hex)) fail('HEX inválido');
      $stmt=$pdo->prepare("UPDATE colores SET nombre=?,hex=? WHERE id_color=?"); $stmt->execute([$nombre,$hex?:null,$id]);
      
      $changes = array_diff_assoc_recursive($old_data, ['nombre' => $nombre, 'hex' => $hex?:null, 'id_color' => $id]);
      if (!empty($changes)) { Auditor::log($userId, 'colores', $id, 'UPDATE', $changes); } // AUDITORÍA
      
      respond(true);
    }
    case 'color_delete': {
      if (!$userId) fail('Usuario no autenticado para esta acción.'); // Seguridad
      $id = (int)($_POST['id_color'] ?? 0); if(!$id) fail('ID inválido');
      $old_data = get_color_data($pdo, $id);
      $pdo->prepare("DELETE FROM colores WHERE id_color=?")->execute([$id]); 
      Auditor::log($userId, 'colores', $id, 'DELETE', ['registro_eliminado' => $old_data['nombre'] ?? 'ID ' . $id]); // AUDITORÍA
      respond(true);
    }

    /* ================== REPORTES (PDF) ================== */
    case 'report_inventario': {
      // Verifica TCPDF disponible
      if (!class_exists('TCPDF')) { while (ob_get_level() > 0) { ob_end_clean(); } http_response_code(500); echo "TCPDF no está disponible. Instala con Composer (tecnickcom/tcpdf) o coloca tcpdf.php en vendor/tecnickcom/tcpdf/."; exit; }

      while (ob_get_level() > 0) { ob_end_clean(); }

      $q = trim($_GET['q'] ?? '');
      $sql = "SELECT p.id_producto,p.sku,p.nombre,p.descripcion,p.costo_base,p.activo,p.creado_en,p.actualizado_en,
                     p.unidades,
                     c.nombre AS categoria, s.nombre AS subcategoria,
                     t.nombre AS talla, col.nombre AS color
              FROM productos p
              JOIN categorias c    ON c.id_categoria=p.id_categoria
              JOIN subcategorias s  ON s.id_subcategoria=p.id_subcategoria
              LEFT JOIN tallas t    ON t.id_talla=p.id_talla
              LEFT JOIN colores col ON col.id_color=p.id_color";
      $params = [];
      if ($q !== '') {
        $sql .= " WHERE p.sku LIKE ? OR p.nombre LIKE ?";
        $like = "%$q%"; $params = [$like, $like];
      }
      $sql .= " ORDER BY p.id_producto DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $rows = $stmt->fetchAll();

      if ($rows && count($rows) > 0) {
        $ids = implode(',', array_map('intval', array_column($rows,'id_producto')));
        $ex  = $pdo->query("SELECT e.id_producto, e.cantidad, b.nombre AS bodega
                            FROM existencias e
                            JOIN bodegas b ON b.id_bodega=e.id_bodega
                            WHERE e.id_producto IN ($ids)")->fetchAll();
        $grp = [];
        foreach($ex as $r){ $grp[$r['id_producto']][] = ['bodega'=>$r['bodega'], 'cantidad'=>$r['cantidad']]; }
        foreach($rows as &$r){ $r['existencias'] = $grp[$r['id_producto']] ?? []; }
        unset($r);
      }

      $pdf = new ProductPdf('Inventario - Reporte');
      $pdf->output($rows, 'SIRIO S.A. DE C.V.', $q !== '' ? $q : null);
      exit;
    }

    case 'report_producto': {
      // Verifica TCPDF disponible
      if (!class_exists('TCPDF')) { while (ob_get_level() > 0) { ob_end_clean(); } http_response_code(500); echo "TCPDF no está disponible. Instala con Composer (tecnickcom/tcpdf) o coloca tcpdf.php en vendor/tecnickcom/tcpdf/."; exit; }

      while (ob_get_level() > 0) { ob_end_clean(); }

      $id = (int)($_GET['id'] ?? 0);
      if(!$id) { http_response_code(400); echo 'ID inválido'; exit; }

      $stmt = $pdo->prepare("
        SELECT p.*, c.nombre AS categoria, s.nombre AS subcategoria,
               t.nombre AS talla, col.nombre AS color
        FROM productos p
        JOIN categorias c    ON c.id_categoria=p.id_categoria
        JOIN subcategorias s  ON s.id_subcategoria=p.id_subcategoria
        LEFT JOIN tallas t    ON t.id_talla=p.id_talla
        LEFT JOIN colores col ON col.id_color=p.id_color
        WHERE p.id_producto = ?
      ");
      $stmt->execute([$id]);
      $prod = $stmt->fetch();
      if(!$prod){ http_response_code(404); echo 'Producto no encontrado'; exit; }

      $exist = $pdo->prepare("
        SELECT b.nombre AS bodega, e.cantidad
        FROM existencias e
        JOIN bodegas b ON b.id_bodega = e.id_bodega
        WHERE e.id_producto = ?
      ");
      $exist->execute([$id]);
      $existRows = $exist->fetchAll();

      // CONSULTA CORREGIDA: Se asegura que el LEFT JOIN use correctamente el alias 'm' y la columna 'id_usuario'
      $movs = $pdo->prepare("
        SELECT m.fecha_evento, b.nombre AS bodega, m.tipo, m.cantidad, m.costo_unitario, m.motivo, 
               u.full_name AS usuario_nombre, u.email AS usuario_email
        FROM movimientos_inventario m
        JOIN bodegas b ON b.id_bodega = m.id_bodega
        LEFT JOIN users u ON u.id = m.id_usuario /* COLUMNA id_usuario se usa correctamente con alias m */
        WHERE m.id_producto = ?
        ORDER BY m.fecha_evento DESC, m.id_movimiento DESC
      ");
      $movs->execute([$id]);
      $movRows = $movs->fetchAll();

      $pdf = new ProductDetailPdf('Ficha de Producto');
      $pdf->output($prod, $existRows, $movRows, 'SIRIO S.A. DE C.V.');
      exit;
    }

    default:
      fail('Acción no soportada', 404);
  }
} catch (Throwable $e) {
  fail($e->getMessage(), 500);
}