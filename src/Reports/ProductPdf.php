<?php
// src/Reports/ProductPdf.php
declare(strict_types=1);

use TCPDF;

class ProductPdf
{
    private TCPDF $pdf;

    public function __construct(string $title = 'Inventario - Reporte')
    {
        $this->pdf = new TCPDF('L','mm','A4',true,'UTF-8',false); // horizontal para tabla ancha
        $this->pdf->SetCreator('Sistema de Inventario');
        $this->pdf->SetAuthor('SIRIO S.A. DE C.V.');
        $this->pdf->SetTitle($title);
        $this->pdf->SetSubject('Listado de Productos');
        $this->pdf->SetMargins(10,14,10);
        $this->pdf->SetAutoPageBreak(true, 14);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->SetFont('dejavusans','',9,'',true);
    }

    /** @param array<int,array<string,mixed>> $rows */
    public function output(array $rows, string $empresa, ?string $filtroTxt=null): void
    {
        $this->pdf->AddPage();
        $fecha = (new DateTime())->format('d/m/Y H:i');
        $filtro = $filtroTxt ? ' | Filtro: '. $this->esc($filtroTxt) : '';

        $hdr = <<<HTML
          <h2 style="margin:0 0 6px 0; font-size:14px;">{$this->esc($empresa)}</h2>
          <h3 style="margin:0 0 8px 0; font-size:12px;">Inventario de Productos</h3>
          <div style="font-size:9px;color:#666;">Generado: {$this->esc($fecha)}{$filtro}</div>
          <hr style="margin:8px 0 10px 0;" />
        HTML;
        $this->pdf->writeHTML($hdr, true, false, true, false, '');
        $this->pdf->writeHTML($this->table($rows), true, false, true, false, '');
        $this->pdf->Output('inventario.pdf','I');
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function table(array $rows): string
    {
        $head = '
        <thead>
          <tr style="background-color:#F1F5F9;">
            <th width="6%"><b>ID</b></th>
            <th width="12%"><b>SKU</b></th>
            <th width="20%"><b>Producto</b></th>
            <th width="12%"><b>Categoría</b></th>
            <th width="12%"><b>Subcategoría</b></th>
            <th width="10%"><b>Costo</b></th>
            <th><b>Existencias por bodega</b></th>
            <th width="9%"><b>Estado</b></th>
            <th width="10%"><b>Actualizado</b></th>
          </tr>
        </thead>';

        $body = '';
        if (!$rows) {
            $body = '<tr><td colspan="9" style="text-align:center;color:#666;padding:10px;">Sin resultados</td></tr>';
        } else {
            foreach ($rows as $r) {
                $exist = '';
                if (!empty($r['existencias'])) {
                    $items = array_map(
                        fn($e)=> sprintf('%s: %s', $this->esc((string)$e['bodega']), (string)$e['cantidad']),
                        $r['existencias']
                    );
                    $exist = implode('<br>', $items);
                } else {
                    $exist = '<span style="color:#888;">(Sin movimientos)</span>';
                }

                $body .= sprintf(
                    '<tr>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s%s</td>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s</td>
                     </tr>',
                    $this->esc((string)$r['id_producto']),
                    $this->esc((string)$r['sku']),
                    $this->esc((string)$r['nombre']),
                    ($r['talla']||$r['color']) ? '<br><span style="color:#6b7280;font-size:9px;">'.
                        ($r['talla'] ? $this->esc((string)$r['talla']).' • ' : '').
                        ($r['color'] ? $this->esc((string)$r['color']) : '').
                      '</span>' : '',
                    $this->esc((string)$r['categoria']),
                    $this->esc((string)$r['subcategoria']),
                    $r['costo_base']!==null ? number_format((float)$r['costo_base'],2) : '-',
                    $exist,
                    (int)$r['activo']===1 ? 'Activo' : 'Inactivo',
                    $this->esc((string)$r['actualizado_en'])
                );
            }
        }

        return <<<HTML
        <style>
          table { border-collapse: collapse; width:100%; }
          th,td { border-bottom:1px solid #E2E8F0; padding:6px 6px; vertical-align:top; }
          th { text-align:left; }
          tr:nth-child(even) td { background:#FAFAFA; }
        </style>
        <table>{$head}<tbody>{$body}</tbody></table>
        HTML;
    }

    private function esc(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
    }
}
