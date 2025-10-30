<?php
// src/Reports/UserDetailPdf.php
declare(strict_types=1);

use TCPDF;

class UserDetailPdf
{
    private TCPDF $pdf;
    private ?string $logoSource = null;

    public function __construct(string $title = 'Ficha de Usuario')
    {
        // -------------------------------------------------------------
        // INICIALIZACIÓN Y CONVERSIÓN A BASE64 del logo (similar a ProductDetailPdf)
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
        $this->pdf->SetSubject('Detalle de Usuario');

        $this->pdf->SetMargins(10, 10, 10);
        $this->pdf->SetAutoPageBreak(true, 12);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->SetFont('dejavusans', '', 9.5, '', true); 
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }
    
    /**
     * @param array<string,mixed> $user
     * @param array<int,array<string,mixed>> $logs
     * @param array<string,float|int> $movementTotals (Agregamos un nuevo parámetro para los totales)
     */
    public function output(array $user, array $logs, string $empresa): void
    {
        $this->pdf->AddPage();

        // ------- PALETA -------
        $C = [
            'ink'           => '#2D3748', 'muted' => '#718096', 'line' => '#EDF2F7', 'zebra' => '#F7FAFC',
            'primary'       => '#3182CE', 'primary_light' => '#EBF8FF', 'primary_dark' => '#2B6CB0',
            'success'       => '#38A169', 'success_light' => '#F0FFF4', 'danger' => '#E53E3E', 'danger_light' => '#FFF5F5',
            'warning'       => '#D69E2E', 'warning_light' => '#FFFAF0', 'accent' => '#805AD5', 'accent_light' => '#FAF5FF',
        ];

        // ----------------------------------------------------------------------------------
        // >>> DATOS DE EJEMPLO PARA MOVIMIENTOS Y CÁLCULO
        // NOTA: Debes reemplazar estos valores con el cálculo real de tu lógica de negocio
        
        $totalCantidad = 15;
        $promedioCostoUnit = 23.50; // Costo Unit. de ejemplo (podría ser un promedio o simplemente un valor fijo)
        $totalCosto = $totalCantidad * $promedioCostoUnit; // Cálculo del Costo Total
        
        $movementTotals = [
            'cantidad' => $totalCantidad,
            'costo_unit_promedio' => $promedioCostoUnit,
            'costo_total' => $totalCosto,
        ];
        
        // ----------------------------------------------------------------------------------

        // ------- Datos del usuario y encabezado (Sin cambios) -------
        $empresaEsc = $this->esc($empresa);
        $fecha = (new DateTime())->format('d/m/Y H:i');
        $fechaEsc = $this->esc($fecha);
        $idEsc   = $this->esc((string)$user['id']);
        $nameEsc = $this->esc((string)$user['full_name']);
        $emailEsc= $this->esc((string)$user['email']);
        $roleR   = (string)($user['role'] ?? 'employee');
        $created = $this->esc((string)$user['created_at']);
        $logsCount = count($logs);

        $is_admin = $roleR === 'admin';
        $roleTxt = $is_admin ? 'Administrador' : 'Empleado';
        $roleBg = $is_admin ? $C['danger_light'] : $C['primary_light'];
        $roleTx = $is_admin ? $C['danger'] : $C['primary_dark'];

        // =============================================================
        // ====== HEADER: Logo, Marca y Título ======
        // =============================================================
        $logo_html = $this->logoSource ? '<img src="'.$this->logoSource.'" style="width:60px;">' : '';
        
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
<div style="font-size:18px;color:'.$C['ink'].';font-weight:bold;line-height:1.1; margin-bottom:10px;">FICHA DE USUARIO</div>
', true, false, true, false, '');

        // =============================================================
        // ====== HERO SECTION: Información del Usuario ======
        // =============================================================
        $badge = '<span style="background:'.$roleBg.';color:'.$roleTx.';padding:3px 10px;border-radius:999px;font-weight:bold;font-size:9px">'.$roleTxt.'</span>';

        $this->pdf->writeHTML('
<style>
  table.user-meta{width:100%;border-collapse:collapse;font-size:9.5px;line-height:1.4}
  table.user-meta td{padding:3px 0px;vertical-align:top}
  table.user-meta td.lbl{width:20%;color:'.$C['muted'].';font-weight:bold;} 
</style>

<div style="border:1px solid '.$C['line'].'; border-radius:12px; padding:15px; background:#FFFFFF; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <div style="font-size:10px;margin-bottom:8px;">'.$badge.'</div>
    
    <div style="font-size:16px;color:'.$C['ink'].';font-weight:bold;margin-bottom:12px; padding-bottom:5px; border-bottom:1px solid '.$C['line'].';">'.$nameEsc.'</div>

    <table class="user-meta">
        <tr>
            <td class="lbl">ID</td>
            <td style="width:30%">'.$idEsc.'</td>
            <td class="lbl" style="width:20%">Fecha de Creación</td>
            <td style="width:30%">'.$created.'</td>
        </tr>
        <tr>
            <td class="lbl">Email</td>
            <td colspan="3"><span style="font-weight:bold;color:'.$C['primary_dark'].'">'.$emailEsc.'</span></td>
        </tr>
    </table>
    
    <div style="margin-top:15px; padding-top:10px; border-top:1px solid '.$C['line'].';">
        <div style="font-size:10px;color:'.$C['ink'].';font-weight:bold;margin-bottom:5px;">Resumen</div>
        <div style="font-size:9.5px;color:'.$C['muted'].';line-height:1.5;">Este usuario ha realizado <strong>'.$logsCount.'</strong> acciones de auditoría registradas en el sistema.</div>
    </div>
</div>
', true, false, true, false, '');

        $this->pdf->writeHTML('<div style="height:15px;"></div>', true, false, true, false, ''); 

        // =============================================================
        // ====== Logs de Auditoría (Se mantiene sin cambios) ======
        // =============================================================
        $this->pill('Logs de Auditoría', $C);
        $this->pdf->writeHTML($this->tableLogs($logs, $C), true, false, true, false, '');
        
        // =============================================================
        // ====== NUEVA TABLA DE TOTALES DE MOVIMIENTO ======
        // =============================================================
        $this->pdf->writeHTML($this->tableMovementTotals($movementTotals, $C), true, false, true, false, '');

        // ====== Footer (ELIMINADO) ======
        /*
        $this->pdf->SetY(-10);
        $this->pdf->SetFont('dejavusans', '', 8);
        $this->pdf->Cell(0, 8, $empresaEsc.' · '.$fechaEsc.' · Página '.$this->pdf->getAliasNumPage().'/'.$this->pdf->getAliasNbPages(), 0, 0, 'C');
        */

        $file = 'usuario_' . preg_replace('/[^\w\-]+/u', '_', $idEsc) . '.pdf';
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
    private function tableLogs(array $rows, array $C): string
    {
        if (!$rows) return '<div style="color:'.$C['muted'].';font-size:9px;margin-left:4px;padding:10px;border:1px dashed '.$C['line'].';border-radius:8px;background:'.$C['zebra'].';">(Sin acciones registradas por este usuario)</div>';

        $body = '';
        $i = 0;
        foreach ($rows as $m) {
            $bg  = ($i % 2 === 0) ? '#FFFFFF' : $C['zebra'];
            $fecha  = htmlspecialchars((string)$m['fecha_hora'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $tabla  = htmlspecialchars((string)$m['tabla_afectada'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $id_reg = htmlspecialchars((string)$m['registro_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $accion = htmlspecialchars((string)$m['accion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $ip     = htmlspecialchars((string)($m['ip_origen'] ?? '-'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            
            $detail = json_decode((string)($m['detalle_cambios'] ?? '{}'), true);
            $detailTxt = print_r($detail, true);
            $detailHtml = '<pre style="font-size:8px; color:'.$C['muted'].'; margin:0; padding:0; line-height:1.2;">'.$this->esc($detailTxt).'</pre>';

            $accionBg = $C['primary_light']; $accionTx = $C['primary_dark'];
            if ($accion === 'CREATE') { $accionBg = $C['success_light']; $accionTx = $C['success']; }
            if ($accion === 'DELETE') { $accionBg = $C['danger_light']; $accionTx = $C['danger']; }
            if ($accion === 'UPDATE') { $accionBg = $C['warning_light']; $accionTx = $C['warning']; }
            
            $accionBadge = '<span style="background:'.$accionBg.';color:'.$accionTx.';padding:2px 6px;border-radius:4px;font-size:9px;">'.$accion.'</span>';


            $body .= '<tr style="background:'.$bg.'">
                <td style="width:18%;">'.$fecha.'</td>
                <td style="width:10%;">'.$accionBadge.'</td>
                <td style="width:15%;">'.$tabla.' (#'.$id_reg.')</td>
                <td style="width:40%;">'.$detailHtml.'</td>
                <td style="width:17%;">'.$ip.'</td>
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
    <th style="width:18%;"><b>Fecha</b></th><th style="width:10%;"><b>Acción</b></th><th style="width:15%;"><b>Tabla/ID</b></th>
    <th style="width:40%;"><b>Detalle</b></th><th style="width:17%;"><b>IP</b></th>
  </tr></thead>
  <tbody>'.$body.'</tbody>
</table>';
    }

    /** @param array<string,float|int> $totals */
    private function tableMovementTotals(array $totals, array $C): string
    {
        $cantidad = $this->esc((string)($totals['cantidad'] ?? 0));
        $costoUnit = $this->esc('$' . number_format((float)($totals['costo_unit_promedio'] ?? 0), 2));
        $costoTotal = $this->esc('$' . number_format((float)($totals['costo_total'] ?? 0), 2));
        
        $colWidth = '33.333%';

        return '
<div style="margin-top:15px; margin-bottom:5px; font-size:12px;color:'.$C['ink'].';font-weight:bold;">Totales de Movimientos Registrados</div>
<style>
  table.totals-grid{border-collapse:collapse;width:100%;font-size:9.5px;border:1px solid '.$C['line'].';border-radius:8px;overflow:hidden;}
  table.totals-grid thead th{background:'.$C['primary_light'].';border-bottom:2px solid '.$C['primary'].';padding:7px 8px;text-align:center;color:'.$C['primary_dark'].';font-weight:bold;}
  table.totals-grid tbody td{border-bottom:0;padding:8px 8px;color:'.$C['ink'].';font-weight:bold;text-align:center;background:'.$C['zebra'].';}
</style>
<table class="totals-grid">
  <thead><tr>
    <th style="width:'.$colWidth.';"><b>Cantidad Total</b></th>
    <th style="width:'.$colWidth.';"><b>Costo Unit. (Promedio)</b></th>
    <th style="width:'.$colWidth.';"><b>Costo Total</b></th>
  </tr></thead>
  <tbody>
    <tr>
      <td style="width:'.$colWidth.';">'.$cantidad.'</td>
      <td style="width:'.$colWidth.';">'.$costoUnit.'</td>
      <td style="width:'.$colWidth.';background:'.$C['primary_light'].';color:'.$C['primary_dark'].';border-top:1px solid '.$C['primary'].';">'.$costoTotal.'</td>
    </tr>
  </tbody>
</table>';
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}