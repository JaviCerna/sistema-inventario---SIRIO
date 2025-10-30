<?php
// public/api/dashboard.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php'; // 👈 usamos tu singleton

try {
  // --- Auth igual que el resto de tus módulos ---
  $auth = new Auth();
  if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autenticado']);
    exit;
  }

  // --- Obtener PDO desde tu Database.php (sin tocar tus archivos) ---
  $pdo = Database::getInstance()->getConnection();

  // --- Inputs y valores por defecto ---
  $action         = $_GET['action'] ?? '';
  $fecha_desde    = $_GET['desde']  ?? date('Y-m-01');
  $fecha_hasta    = $_GET['hasta']  ?? date('Y-m-d');
  // Aseguramos que los filtros sean null si están vacíos o no presentes
  $id_bodega_f    = isset($_GET['bodega']) && $_GET['bodega'] !== '' ? (int)$_GET['bodega'] : null;
  $id_categoria_f = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? (int)$_GET['categoria'] : null;
  $low_threshold  = isset($_GET['low']) && is_numeric($_GET['low']) ? (float)$_GET['low'] : 10.0;

  $ok  = fn($data, $extra = []) => (print json_encode(array_merge(['ok'=>true,'data'=>$data], $extra), JSON_UNESCAPED_UNICODE));
  $err = function($msg, $code = 400) { http_response_code($code); print json_encode(['ok'=>false,'message'=>$msg], JSON_UNESCAPED_UNICODE); };

  // --- Diagnóstico rápido ---
  if ($action === 'ping') {
    $dbname = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $cat = (int)$pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
    $bod = (int)$pdo->query('SELECT COUNT(*) FROM bodegas')->fetchColumn();
    $prod= (int)$pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    $ok(['db'=>$dbname,'categorias'=>$cat,'bodegas'=>$bod,'productos'=>$prod]);
    exit;
  }

  switch ($action) {
    case 'filters': {
      $bodegas = $pdo->query("SELECT id_bodega, nombre FROM bodegas WHERE activa=1 ORDER BY nombre")->fetchAll();
      $categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias WHERE activa=1 ORDER BY nombre")->fetchAll();
      $ok(['bodegas'=>$bodegas, 'categorias'=>$categorias]);
    } break;

    case 'kpis': {
      $kpi = [
        'categorias'    => (int)$pdo->query("SELECT COUNT(*) FROM categorias WHERE activa=1")->fetchColumn(),
        'subcategorias' => (int)$pdo->query("SELECT COUNT(*) FROM subcategorias WHERE activa=1")->fetchColumn(),
        'bodegas'       => (int)$pdo->query("SELECT COUNT(*) FROM bodegas WHERE activa=1")->fetchColumn(),
        'productos'     => (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE activo=1")->fetchColumn(),
      ];
      $ok($kpi);
    } break;

    case 'chart_bodega': {
      $sql = "
        SELECT b.nombre AS bodega, COALESCE(SUM(e.cantidad),0) AS total
        FROM bodegas b
        LEFT JOIN existencias e ON e.id_bodega=b.id_bodega
        LEFT JOIN productos  p ON p.id_producto=e.id_producto
        WHERE b.activa=1
      ";
      $params = [];
      
      // 💥 CORRECCIÓN 1: Aplicar filtro de Bodega en la cláusula WHERE
      if ($id_bodega_f) { 
        $sql .= " AND b.id_bodega=:bod "; 
        $params[':bod']=$id_bodega_f; 
      }
      
      // Filtro de Categoría (ya implementado y correcto)
      if ($id_categoria_f) { 
        $sql .= " AND p.id_categoria=:cat "; 
        $params[':cat']=$id_categoria_f; 
      }
      
      $sql .= " GROUP BY b.id_bodega ORDER BY b.nombre";
      $st = $pdo->prepare($sql); $st->execute($params);
      $ok($st->fetchAll());
    } break;

    case 'chart_categoria': {
      // Productos por categoría, contando solo los productos que tienen existencias
      // o que coinciden con la bodega filtrada.
      $sql = "
        SELECT c.nombre AS categoria, COUNT(DISTINCT p.id_producto) AS total
        FROM categorias c
        JOIN productos p ON p.id_categoria=c.id_categoria AND p.activo=1
        LEFT JOIN existencias e ON e.id_producto=p.id_producto
        WHERE c.activa=1
      ";
      $params = [];
      // CORRECCIÓN: Si se filtra por bodega, solo contamos productos con existencia en esa bodega.
      if ($id_bodega_f) {
        $sql .= " AND e.id_bodega = :bod ";
        $params[':bod'] = $id_bodega_f;
      }
      $sql .= " GROUP BY c.id_categoria ORDER BY c.nombre";
      $st = $pdo->prepare($sql); $st->execute($params);
      $ok($st->fetchAll());
    } break;

    case 'serie_ingresos': {
      $sql = "
        SELECT DATE(mi.fecha_evento) AS fecha, SUM(mi.cantidad) AS total
        FROM movimientos_inventario mi
        JOIN productos p ON p.id_producto=mi.id_producto
        WHERE mi.tipo='IN'
          AND DATE(mi.fecha_evento) BETWEEN :desde AND :hasta
      ";
      $params = [':desde'=>$fecha_desde, ':hasta'=>$fecha_hasta];
      if ($id_bodega_f)    { $sql .= " AND mi.id_bodega = :bod "; $params[':bod']=$id_bodega_f; }
      if ($id_categoria_f) { $sql .= " AND p.id_categoria = :cat "; $params[':cat']=$id_categoria_f; }
      $sql .= " GROUP BY DATE(mi.fecha_evento) ORDER BY DATE(mi.fecha_evento)";
      $st = $pdo->prepare($sql); $st->execute($params);
      $ok($st->fetchAll());
    } break;

    case 'ultimos_ingresos': {
      $sql = "
        SELECT mi.id_movimiento, mi.fecha_evento, mi.cantidad, mi.costo_unitario,
               b.nombre AS bodega, p.sku, p.nombre AS producto
        FROM movimientos_inventario mi
        JOIN bodegas b ON b.id_bodega=mi.id_bodega
        JOIN productos p ON p.id_producto=mi.id_producto
        WHERE mi.tipo='IN'
          AND DATE(mi.fecha_evento) BETWEEN :desde AND :hasta
      ";
      $params = [':desde'=>$fecha_desde, ':hasta'=>$fecha_hasta];
      if ($id_bodega_f)    { $sql .= " AND mi.id_bodega = :bod "; $params[':bod']=$id_bodega_f; }
      if ($id_categoria_f) { $sql .= " AND p.id_categoria = :cat "; $params[':cat']=$id_categoria_f; }
      $sql .= " ORDER BY mi.fecha_evento DESC LIMIT 12";
      $st = $pdo->prepare($sql); $st->execute($params);
      $ok($st->fetchAll());
    } break;

    case 'stock_bajo': {
      $sql = "
        SELECT b.nombre AS bodega, p.sku, p.nombre AS producto,
               e.cantidad, e.stock_minimo
        FROM existencias e
        JOIN bodegas b ON b.id_bodega=e.id_bodega
        JOIN productos p ON p.id_producto=e.id_producto
        WHERE 1=1
      ";
      $params = [];
      if ($id_bodega_f)    { $sql .= " AND e.id_bodega=:bod "; $params[':bod']=$id_bodega_f; }
      if ($id_categoria_f) { $sql .= " AND p.id_categoria=:cat "; $params[':cat']=$id_categoria_f; }
      $sql .= " AND (e.cantidad <= e.stock_minimo OR (e.stock_minimo=0 AND e.cantidad <= :thr))";
      $params[':thr'] = $low_threshold;
      $sql .= " ORDER BY e.cantidad ASC, b.nombre, p.nombre";
      $st = $pdo->prepare($sql); $st->execute($params);
      $ok($st->fetchAll(), ['threshold'=>$low_threshold]);
    } break;

    default:
      $err('Acción no soportada', 404);
  }

} catch (Throwable $e) {
  // Asegura que el mensaje de error del servidor se envíe al frontend para depuración
  http_response_code(500);
  error_log("API Error: " . $e->getMessage()); // Loguea el error internamente
  echo json_encode(['ok'=>false,'message'=>'Error del servidor','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}