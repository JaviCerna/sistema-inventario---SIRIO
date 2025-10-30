<?php
declare(strict_types=1);

// DEBUG opcional
if (isset($_GET['debug'])) { ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL); }

// Autoload (Composer) o incluye tcpdf.php manualmente si no usas Composer
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
  if (!class_exists(\TCPDF::class)) {
    header('Content-Type: text/plain; charset=utf-8');
    die("TCPDF no está disponible. Ejecuta: composer require tecnickcom/tcpdf");
  }
} else {
  // Alternativa sin Composer (ajusta la ruta a tu tcpdf.php)
  $tcpdfPhp = __DIR__ . '/../vendor/tcpdf/tcpdf.php';
  if (!file_exists($tcpdfPhp)) {
    header('Content-Type: text/plain; charset=utf-8');
    die("No se encontró TCPDF. Instálalo con Composer o coloca tcpdf.php en: $tcpdfPhp");
  }
  require_once $tcpdfPhp;
}

require_once __DIR__ . '/../src/Database.php';

// 1) Consultar datos
$q = trim((string)($_GET['q'] ?? ''));
try {
  $pdo = Database::getInstance()->getConnection();
  $sql = "SELECT id_categoria, nombre, descripcion, activa, creada_en, actualizada_en FROM categorias";
  $params = [];
  if ($q !== '') {
    $sql .= " WHERE nombre LIKE :q OR descripcion LIKE :q2";
    $params[':q']  = "%$q%";
    $params[':q2'] = "%$q%";
  }
  $sql .= " ORDER BY creada_en DESC";
  $st = $pdo->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    die("ERROR DB: ".$e->getMessage());
  }
  http_response_code(500);
  die("Error al consultar categorías.");
}

// 2) Construir HTML simple para TCPDF (usa subset de HTML/CSS)
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fecha = date('Y-m-d H:i');
$titulo = 'Reporte de Categorías';
$subtitulo = $q !== '' ? ("Filtro: ".$e($q)) : "Todas las categorías";
$total = count($rows);
$act=0; $ina=0; foreach ($rows as $r) { ((int)$r['activa']===1) ? $act++ : $ina++; }

// Cabecera HTML
$head = '
<style>
h1{font-size:18pt;margin:0 0 4px 0}
.small{color:#666;font-size:9pt;margin:0 0 6px 0}
.kpis{font-size:10pt;margin:4px 0 8px 0}
table{width:100%; border-collapse:collapse; font-size:9pt}
th{background-color:#f0f0f0; font-weight:bold; border:1px solid #ccc; padding:5px; text-align:left}
td{border:1px solid #ccc; padding:5px; vertical-align:top}
.badge{display:inline-block; padding:2px 6px; border-radius:8px; border:1px solid #bbb; font-size:8pt}
.ok{background-color:#e8f5e9; color:#2e7d32; border-color:#c8e6c9}
.off{background-color:#eceff1; color:#455a64}
.brand{font-size:9pt;color:#444}
</style>
<div class="brand">SIRIO S.A. DE C.V.</div>
<h1>'.$e($titulo).'</h1>
<div class="small">'.$e($subtitulo).' | Generado: '.$e($fecha).'</div>
<div class="kpis"><b>Total:</b> '.$e((string)$total).' &nbsp; <b>Activas:</b> '.$e((string)$act).' &nbsp; <b>Inactivas:</b> '.$e((string)$ina).'</div>
<table cellpadding="0" cellspacing="0">
<thead>
<tr>
  <th style="width:7%;">ID</th>
  <th style="width:22%;">Nombre</th>
  <th>Descripción</th>
  <th style="width:12%;">Estado</th>
  <th style="width:16%;">Creada</th>
  <th style="width:16%;">Actualizada</th>
</tr>
</thead>
<tbody>
';

$body = '';
if (!$rows) {
  $body .= '<tr><td colspan="6" style="text-align:center;color:#777;">Sin resultados</td></tr>';
} else {
  foreach ($rows as $r) {
    $activa = ((int)($r['activa'] ?? 0) === 1);
    $body .= '<tr>
      <td>'.$e($r['id_categoria']).'</td>
      <td>'.$e($r['nombre']).'</td>
      <td>'.$e($r['descripcion'] ?? '').'</td>
      <td><span class="badge '.($activa?'ok':'off').'">'.($activa?'Activa':'Inactiva').'</span></td>
      <td>'.$e($r['creada_en']).'</td>
      <td>'.$e($r['actualizada_en']).'</td>
    </tr>';
  }
}

$html = $head . $body . '</tbody></table>';

// 3) DEBUG HTML (ver en navegador)
if (isset($_GET['debug'])) {
  header('Content-Type: text/html; charset=utf-8');
  echo $html;
  exit;
}

// 4) Generar PDF con TCPDF
// Evitar basura previa
if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

// Crear PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SIRIO');
$pdf->SetAuthor('SIRIO S.A. DE C.V.');
$pdf->SetTitle('Reporte de Categorías');
$pdf->SetSubject('Listado de categorías');

// Márgenes y header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(10, 10, 10);
$pdf->SetFooterMargin(12);

// Fuente (DejaVu Sans soporta UTF-8/acentos)
$pdf->SetFont('dejavusans', '', 10);

// Página
$pdf->AddPage();

// Escribir HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Footer con paginación
$pdf->setFooterFont(['dejavusans','',8]);
// (TCPDF añade el footer con número de página automáticamente si no lo desactivas)

// Salida: descargar
$filename = 'reporte_categorias_'.date('Ymd_His').'.pdf';
$pdf->Output($filename, 'D'); // 'D' = Download, 'I' = inline
exit;
