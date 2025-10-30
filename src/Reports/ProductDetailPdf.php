<?php
// src/Reports/ProductDetailPdf.php
declare(strict_types=1);

use TCPDF;

class ProductDetailPdf
{
    private TCPDF $pdf;
    private ?string $logoSource = null; 

    public function __construct(string $title = 'Ficha de Producto')
    {
        // -------------------------------------------------------------
        // INICIALIZACIÓN Y CONVERSIÓN A BASE64 para máxima compatibilidad del logo
        // -------------------------------------------------------------
        $logo_path_resolved = __DIR__ . '/../../public/assets/sirio-logo.png';
        
        if (file_exists($logo_path_resolved)) {
            $logo_data = file_get_contents($logo_path_resolved);
            if ($logo_data !== false) {
                $mime = mime_content_type($logo_path_resolved);
                $this->logoSource = 'data:' . $mime . ';base64,' . base64_encode($logo_data);
            }
        }
        // -------------------------------------------------------------
        
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('Sistema de Inventario');
        $this->pdf->SetAuthor('SIRIO S.A. DE C.V.');
        $this->pdf->SetTitle($title);
        $this->pdf->SetSubject('Detalle de Producto');

        // Márgenes y configuración
        $this->pdf->SetMargins(10, 10, 10);
        $this->pdf->SetAutoPageBreak(true, 12);

        $this->pdf->setFontSubsetting(true);
        $this->pdf->SetFont('dejavusans', '', 9.5, '', true); 

        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }
    
    // Dejamos setLogoPath por si se quiere inyectar una ruta externa
    public function setLogoPath(?string $path): void
    {
        if ($path && @is_file($path)) {
            $logo_data = file_get_contents($path);
            if ($logo_data !== false) {
                $mime = mime_content_type($path);
                $this->logoSource = 'data:' . $mime . ';base64,' . base64_encode($logo_data);
            }
        } else {
            $this->logoSource = null;
        }
    }

    /**
     * @param array<string,mixed> $prod
     * @param array<int,array<string,mixed>> $exist
     * @param array<int,array<string,mixed>> $movs
     */
    public function output(array $prod, array $exist, array $movs, string $empresa): void
    {
        $this->pdf->AddPage();

        // -------------------------------------------------------------
        // ------- PALETA (sin cambios) -------
        // -------------------------------------------------------------
        $C = [
            'ink'           => '#2D3748', 'muted' => '#718096', 'line' => '#EDF2F7', 'zebra' => '#F7FAFC',
            'primary'       => '#3182CE', 'primary_light' => '#EBF8FF', 'primary_dark' => '#2B6CB0',
            'success'       => '#38A169', 'success_light' => '#F0FFF4', 'danger' => '#E53E3E', 'danger_light' => '#FFF5F5',
            'warning'       => '#D69E2E', 'warning_light' => '#FFFAF0', 'accent' => '#805AD5', 'accent_light' => '#FAF5FF',
        ];

        // ------- Datos (con modificaciones en formato de números) -------
        $empresaEsc = $this->esc($empresa);
        $fecha = (new DateTime())->format('d/m/Y H:i');
        $fechaEsc = $this->esc($fecha);
        $idEsc   = $this->esc((string)$prod['id_producto']);
        $skuEsc  = $this->esc((string)$prod['sku']);
        $nombre  = $this->esc((string)$prod['nombre']);
        $cat     = $this->esc((string)$prod['categoria']);
        $subcat  = $this->esc((string)$prod['subcategoria']);
        $tallaR  = (string)($prod['talla'] ?? '');
        $colorR  = (string)($prod['color'] ?? '');
        $talla   = $this->esc($tallaR);
        $color   = $this->esc($colorR);
        $sepTC   = ($tallaR !== '' && $colorR !== '') ? ' • ' : '';
        // Costo base: CON decimales (2)
        $costo   = $prod['costo_base'] !== null ? number_format((float)$prod['costo_base'], 2) : '-';
        // Unidades definidas: SIN decimales
        $unid    = $this->esc((string)((int)($prod['unidades'] ?? 0)));
        $creado  = $this->esc((string)$prod['creado_en']);
        $update  = $this->esc((string)$prod['actualizado_en']);
        $desc    = $this->esc((string)($prod['descripcion'] ?? '—'));

        $activo  = (int)($prod['activo'] ?? 0);
        $badgeBg = $activo ? $C['success_light'] : $C['danger_light'];
        $badgeTx = $activo ? $C['success'] : $C['danger'];
        $badgeTxT= $activo ? 'Activo' : 'Inactivo';

        $bodegasCount = count($exist);
        $totalExist = 0.0;
        foreach ($exist as $e) { $totalExist += (float)($e['cantidad'] ?? 0); }
        $movsCount = count($movs);

        // =============================================================
        // ====== HEADER: Logo Izq (DOBLE TAMAÑO), Marca Der, Título Abajo ======
        // =============================================================
        $logo_html = $this->logoSource ? '<img src="'.$this->logoSource.'" style="width:60px;">' : ''; // <-- CAMBIO AQUÍ: width:60px
        
        $this->pdf->writeHTML('
<div style="padding-bottom:5px;">
  <table width="100%">
    <tr>
      <td width="50%" style="vertical-align:middle; text-align:left;">
        <table cellspacing="0" cellpadding="0" border="0" align="left">
            <tr>
                <td style="vertical-align:middle; padding-right:2px;">'.$logo_html.'</td> </tr>
        </table>
      </td>
      <td width="50%" style="vertical-align:middle; text-align:right;">
        <div style="font-size:12px;color:'.$C['primary_dark'].';font-weight:bold;">'.$empresaEsc.'</div>
        <div style="font-size:8px;color:'.$C['muted'].';margin-top:2px;">Generado: '.$fechaEsc.'</div>
      </td>
    </tr>
  </table>
</div>
<div style="border-bottom:2px solid '.$C['primary'].'; margin-bottom:5px;"></div>
<div style="font-size:18px;color:'.$C['ink'].';font-weight:bold;line-height:1.1; margin-bottom:10px;">FICHA DE PRODUCTO</div>
', true, false, true, false, '');

        // =============================================================
        // ====== HERO SECTION: Información del Producto (sin cambios) ======
        // =============================================================
        $badge = '<span style="background:'.$badgeBg.';color:'.$badgeTx.';padding:3px 10px;border-radius:999px;font-weight:bold;font-size:9px">'.$badgeTxT.'</span>';

        $this->pdf->writeHTML('
<style>
  table.prod-meta{width:100%;border-collapse:collapse;font-size:9.5px;line-height:1.4}
  table.prod-meta td{padding:3px 0px;vertical-align:top}
  table.prod-meta td.lbl{width:25%;color:'.$C['muted'].';font-weight:bold;} 
</style>

<div style="border:1px solid '.$C['line'].'; border-radius:12px; padding:15px; background:#FFFFFF; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <div style="font-size:10px;margin-bottom:8px;">'.$badge.'</div>
    
    <div style="font-size:16px;color:'.$C['ink'].';font-weight:bold;margin-bottom:12px; padding-bottom:5px; border-bottom:1px solid '.$C['line'].';">'.$nombre.'</div>

    <table class="prod-meta">
        <tr>
            <td class="lbl">SKU / ID</td>
            <td style="width:25%">'.$skuEsc.' / '.$idEsc.'</td>
            <td class="lbl" style="width:20%">Unidades definidas</td>
            <td style="width:30%">'.$unid.'</td>
        </tr>
        <tr>
            <td class="lbl">Categoría / Subcat.</td>
            <td>'.$cat.' / '.$subcat.'</td>
            <td class="lbl">Creado / Actualizado</td>
            <td>'.$creado.' • '.$update.'</td>
        </tr>
        <tr>
            <td class="lbl">Talla / Color</td>
            <td><span style="font-weight:bold;color:'.$C['ink'].'">'.$talla.$sepTC.$color.'</span></td>
            <td class="lbl">Costo base (USD)</td>
            <td colspan="3"><span style="font-size:10.5px;color:'.$C['primary'].';font-weight:bold;">$'.$this->esc($costo).'</span></td>
        </tr>
    </table>
    
    <div style="margin-top:15px; padding-top:10px; border-top:1px solid '.$C['line'].';">
        <div style="font-size:10px;color:'.$C['ink'].';font-weight:bold;margin-bottom:5px;">Descripción detallada</div>
        <div style="font-size:9.5px;color:'.$C['muted'].';line-height:1.5;">'.$desc.'</div>
    </div>
</div>
', true, false, true, false, '');

        $this->pdf->writeHTML('<div style="height:15px;"></div>', true, false, true, false, ''); 

        // =============================================================
        // ====== STAT CARDS / KPIs (Existencias Totales SIN decimales) ======
        // =============================================================
        $this->pdf->writeHTML('
<style>
    .kpi-card{
        border:1px solid '.$C['line'].';
        border-radius:10px;
        padding:10px 12px;
        margin-bottom:8px;
        text-align:left;
    }
    .kpi-value{font-size:20px;font-weight:bold;line-height:1;}
    .kpi-label{font-size:9px;color:'.$C['muted'].';margin-top:2px;}
</style>
<table width="100%">
    <tr>
        <td width="33.33%" style="padding-right:5px;">
            <div class="kpi-card" style="border:1px solid '.$C['primary'].'; background:'.$C['primary_light'].';">
                <div class="kpi-label">Existencias Totales</div>
                <div class="kpi-value" style="color:'.$C['primary_dark'].';">'.number_format($totalExist, 0).'</div>
            </div>
        </td>
        <td width="33.33%" style="padding:0 5px;">
            <div class="kpi-card" style="border:1px solid '.$C['warning'].'; background:'.$C['warning_light'].';">
                <div class="kpi-label">Movimientos Recientes</div>
                <div class="kpi-value" style="color:'.$C['warning'].';">'.$movsCount.'</div>
            </div>
        </td>
        <td width="33.33%" style="padding-left:5px;">
            <div class="kpi-card" style="border:1px solid '.$C['accent'].'; background:'.$C['accent_light'].';">
                <div class="kpi-label">Bodegas con Stock</div>
                <div class="kpi-value" style="color:'.$C['accent'].';">'.$bodegasCount.'</div>
            </div>
        </td>
    </tr>
</table>
', true, false, true, false, '');

        $this->pdf->writeHTML('<div style="height:5px;"></div>', true, false, true, false, '');

        // =============================================================
        // ====== Secciones de tablas (sin cambios) ======
        // =============================================================
        $this->pill('Existencias por Bodega', $C);
        $this->pdf->writeHTML($this->tableExist($exist, $C), true, false, true, false, '');

        $this->pill('Movimientos Registrados', $C);
        $this->pdf->writeHTML($this->tableMovs($movs, $C), true, false, true, false, '');

        // ====== Footer (ELIMINADO) ======
        /*
        $this->pdf->SetY(-10);
        $this->pdf->SetFont('dejavusans', '', 8);
        $this->pdf->Cell(0, 8, $empresaEsc.' · '.$fechaEsc.' · Página '.$this->pdf->getAliasNumPage().'/'.$this->pdf->getAliasNbPages(), 0, 0, 'C');
        */

        $file = 'producto_' . preg_replace('/[^\w\-]+/u', '_', $idEsc) . '.pdf';
        $this->pdf->Output($file, 'I');
    }

    // -------------------------------------------------------------
    // ------- Funciones auxiliares (pill y tablas)
    // -------------------------------------------------------------

    private function pill(string $txt, array $C): void
    {
        $this->pdf->writeHTML(
            '<div style="margin:15px 0 8px 0;">
               <div style="font-size:12px;color:'.$C['ink'].';font-weight:bold;margin-bottom:4px;">'.$this->esc($txt).'</div>
               <div style="height:1px;background:'.$C['line'].';width:100%;"></div>
             </div>',
            true, false, true, false, ''
        );
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function tableExist(array $rows, array $C): string
    {
        if (!$rows) return '<div style="color:'.$C['muted'].';font-size:9px;margin-left:4px;padding:10px;border:1px dashed '.$C['line'].';border-radius:8px;background:'.$C['zebra'].';">(Sin existencias registradas)</div>';

        $body = '';
        $i = 0;
        foreach ($rows as $r) {
            $bg = ($i % 2 === 0) ? '#FFFFFF' : $C['zebra'];
            $bodega = htmlspecialchars((string)$r['bodega'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Cantidad: SIN decimales
            $cant   = htmlspecialchars(number_format((float)($r['cantidad'] ?? 0), 0), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $body  .= '<tr style="background:'.$bg.'"><td style="width:70%;">'.$bodega.'</td><td style="text-align:right;width:30%;">'.$cant.'</td></tr>';
            $i++;
        }

        return '
<style>
  table.grid{border-collapse:collapse;width:60%;font-size:9.5px;border:1px solid '.$C['line'].';border-radius:8px;overflow:hidden;}
  table.grid thead th{background:'.$C['primary_light'].';border-bottom:1px solid '.$C['primary'].';padding:7px 8px;text-align:left;color:'.$C['primary_dark'].';font-weight:bold;}
  table.grid td{border-bottom:1px solid '.$C['line'].';padding:7px 8px;color:'.$C['ink'].';}
  table.grid tbody tr:last-child td{border-bottom:0;}
</style>
<table class="grid">
  <thead><tr><th style="text-align:left;width:70%;"><b>Bodega</b></th><th style="text-align:right;width:30%;"><b>Cantidad</b></th></tr></thead>
  <tbody>'.$body.'</tbody>
</table>';
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function tableMovs(array $rows, array $C): string
    {
        if (!$rows) return '<div style="color:'.$C['muted'].';font-size:9px;margin-left:4px;padding:10px;border:1px dashed '.$C['line'].';border-radius:8px;background:'.$C['zebra'].';">(Sin movimientos registrados)</div>';

        $body = '';
        $i = 0;
        foreach ($rows as $m) {
            $bg  = ($i % 2 === 0) ? '#FFFFFF' : $C['zebra'];
            $fecha  = htmlspecialchars((string)$m['fecha_evento'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $bodega = htmlspecialchars((string)$m['bodega'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $tipo   = htmlspecialchars((string)$m['tipo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Cantidad: SIN decimales
            $cant   = htmlspecialchars(number_format((float)($m['cantidad'] ?? 0), 0), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Costo unitario: CON decimales (4)
            $costo  = $m['costo_unitario'] !== null ? number_format((float)$m['costo_unitario'], 2) : '-';
            $motivo = htmlspecialchars((string)($m['motivo'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $fechaInline = str_replace(["\r","\n"], ' ', $fecha);

            $body .= '<tr style="background:'.$bg.'">
                <td style="white-space:nowrap;width:15%;">'.$fechaInline.'</td>
                <td style="width:18%;">'.$bodega.'</td>
                <td style="width:12%;">'.$tipo.'</td>
                <td style="text-align:right;width:10%;">'.$cant.'</td>
                <td style="text-align:right;width:15%;">'.$costo.'</td>
                <td style="width:30%;">'.$motivo.'</td>
            </tr>';
            $i++;
        }

        return '
<style>
  table.grid{border-collapse:collapse;width:100%;font-size:9.5px;border:1px solid '.$C['line'].';border-radius:8px;overflow:hidden;}
  table.grid thead th{background:'.$C['primary_light'].';border-bottom:1px solid '.$C['primary'].';padding:7px 8px;text-align:left;color:'.$C['primary_dark'].';font-weight:bold;}
  table.grid td{border-bottom:1px solid '.$C['line'].';padding:7px 8px;color:'.$C['ink'].';}
  table.grid tbody tr:last-child td{border-bottom:0;}
</style>
<table class="grid">
  <thead><tr>
    <th style="width:15%;"><b>Fecha evento</b></th><th style="width:18%;"><b>Bodega</b></th><th style="width:12%;"><b>Tipo</b></th>
    <th style="text-align:right;width:10%;"><b>Cantidad</b></th>
    <th style="text-align:right;width:15%;"><b>Costo unit.</b></th>
    <th style="width:30%;"><b>Motivo</b></th>
  </tr></thead>
  <tbody>'.$body.'</tbody>
</table>';
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}