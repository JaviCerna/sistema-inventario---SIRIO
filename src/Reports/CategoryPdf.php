<?php
// src/Reports/CategoryPdf.php
declare(strict_types=1);

use TCPDF;
use DateTime; 

class CategoryPdf
{
    private TCPDF $pdf;
    private ?string $logoSource = null; 

    public function __construct(string $title = 'Categorías - Reporte')
    {
        // -------------------------------------------------------------
        // INICIALIZACIÓN Y CONVERSIÓN A BASE64 del logo (SIN CAMBIOS)
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
        $this->pdf->SetSubject('Listado de Categorías');
        // Márgenes ajustados
        $this->pdf->SetMargins(10, 10, 10); 
        $this->pdf->SetAutoPageBreak(true, 12);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->SetFont('dejavusans', '', 9.5, '', true);
        
        // Desactivar header/footer automático
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    /** @param array<int, array<string,mixed>> $rows */
    public function output(array $rows, string $empresa, ?string $filtro = null): void
    {
        $this->pdf->AddPage();
        
        // Paleta de colores
        $C = [
            'ink'           => '#2D3748', 'muted' => '#718096', 'line' => '#EDF2F7', 
            'primary'       => '#3182CE', 'primary_dark' => '#2B6CB0', 'primary_light' => '#EBF8FF',
            'success'       => '#38A169', 'danger' => '#E53E3E', 'zebra' => '#F7FAFC'
        ];

        $empresaEsc = $this->esc($empresa);
        $fecha = (new DateTime('now'))->format('d/m/Y H:i');
        $fechaEsc = $this->esc($fecha);
        $filtroTxt = $filtro ? ' | Filtro: ' . $this->esc($filtro) : '';
        
        // Header con logo, marca y fecha
        $logo_html = $this->logoSource ? '<img src="'.$this->logoSource.'" style="width:60px;">' : '';
        
        $headerHtml = '
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
        <div style="font-size:8px;color:'.$C['muted'].';margin-top:2px;">Generado: '.$fechaEsc.$filtroTxt.'</div>
      </td>
    </tr>
  </table>
</div>
<div style="border-bottom:2px solid '.$C['primary'].'; margin-bottom:5px;"></div>
<div style="font-size:18px;color:'.$C['ink'].';font-weight:bold;line-height:1.1; margin-bottom:10px;">LISTADO DE CATEGORÍAS</div>
'; 

        $this->pdf->writeHTML($headerHtml, true, false, true, false, '');
        
        // Separador adicional para alejar la tabla del título (SOLUCIÓN 1)
        $this->pdf->writeHTML('<div style="height:5px;"></div>', true, false, true, false, '');

        // Generar tabla
        $this->pdf->writeHTML($this->buildTable($rows, $C), true, false, true, false, '');

        $this->pdf->Output('categorias.pdf', 'I');
    }

    /** @param array<int, array<string,mixed>> $rows */
    private function buildTable(array $rows, array $C): string
    {
        // Encabezado
        $head = '
        <thead>
          <tr style="background-color:'.$C['primary_light'].';">
            <th width="7%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>ID</b></th>
            <th width="18%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>Nombre</b></th>
            <th width="35%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>Descripción</b></th>
            <th width="10%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>Estado</b></th>
            <th width="15%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>Creada</b></th>
            <th width="15%" style="border-bottom:1px solid '.$C['primary'].'; color:'.$C['primary_dark'].';"><b>Actualizada</b></th>
          </tr>
        </thead>';

        $body = '';
        if (!$rows) {
            // Mensaje de sin resultados con margen superior aumentado
            return '<div style="margin-top:20px; color:'.$C['muted'].';font-size:9.5px;padding:10px;border:1px dashed '.$C['line'].';border-radius:8px;background:'.$C['zebra'].';">(Sin categorías registradas)</div>';
        } else {
            $i = 0;
            foreach ($rows as $r) {
                $bg = ($i % 2 === 0) ? '#FFFFFF' : $C['zebra'];
                $activo = (int)($r['activa'] ?? 0);
                
                // Badge de estado con color
                if ($activo === 1) {
                    $statusHtml = '<span style="color:'.$C['success'].';font-weight:bold;">Activa</span>';
                } else {
                    $statusHtml = '<span style="color:'.$C['danger'].';font-weight:bold;">Inactiva</span>';
                }

                $body .= sprintf(
                    '<tr style="background-color:'.$bg.';">
                        <td width="7%%">%s</td>
                        <td width="18%%">%s</td>
                        <td width="35%%">%s</td>
                        <td width="10%%">%s</td>
                        <td width="15%%">%s</td>
                        <td width="15%%">%s</td>
                    </tr>',
                    $this->esc((string)$r['id_categoria']),
                    $this->esc((string)$r['nombre']),
                    $this->esc((string)($r['descripcion'] ?? '')),
                    $statusHtml,
                    $this->esc((string)$r['creada_en']),
                    $this->esc((string)$r['actualizada_en'])
                );
                $i++;
            }
        }

        // Estilos ajustados para aplicar bordes y margen directamente a la tabla (SOLUCIÓN 2)
        return '
        <style>
          table.grid-style { 
            border-collapse: collapse; 
            width: 100%; /* Ocupa todo el ancho */
            font-size:9.5px; 
            margin: 0; /* Asegura la alineación izquierda */
            padding: 0;
            
            /* Aplicamos los estilos de borde del contenedor directamente a la tabla */
            border: 1px solid '.$C['line'].'; 
            /* El border-radius en la tabla puede ser inconsistente, pero lo intentamos */
            border-radius: 8px; 
          }
          table.grid-style thead, table.grid-style tbody {
            margin: 0;
            padding: 0;
          }
          table.grid-style td { 
            border-bottom: 1px solid '.$C['line'].'; 
            padding: 7px 8px; 
            vertical-align: top; 
            color:'.$C['ink'].'; 
          }
          table.grid-style th {
            padding: 7px 8px; 
            text-align: left;
            font-weight:bold;
          }
          /* El borde del header ahora debe ser visible */
          table.grid-style thead tr:first-child th {
              border-top: none; 
          }
          /* El borde de la última fila se elimina para el efecto "tarjeta" */
          table.grid-style tbody tr:last-child td { border-bottom: none; }
        </style>
        <table class="grid-style">
              '.$head.'
              <tbody>'.$body.'</tbody>
        </table>'; // <-- Ya no usamos el div.table-container
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}