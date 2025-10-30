<?php
// src/Reports/UserPdf.php
declare(strict_types=1);

use TCPDF;

class UserPdf
{
    private TCPDF $pdf;

    public function __construct(string $title = 'Usuarios - Reporte General')
    {
        $this->pdf = new TCPDF('P','mm','A4',true,'UTF-8',false); // P=Vertical
        $this->pdf->SetCreator('Sistema de Inventario');
        $this->pdf->SetAuthor('SIRIO S.A. DE C.V.');
        $this->pdf->SetTitle($title);
        $this->pdf->SetSubject('Listado de Usuarios');
        $this->pdf->SetMargins(10,14,10);
        $this->pdf->SetAutoPageBreak(true, 14);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->SetFont('dejavusans','',10,'',true);
    }

    /** @param array<int,array<string,mixed>> $rows */
    public function output(array $rows, string $empresa, ?string $filtroTxt=null): void
    {
        $this->pdf->AddPage();
        $fecha = (new DateTime())->format('d/m/Y H:i');
        $filtro = $filtroTxt ? ' | Filtro: '. $this->esc($filtroTxt) : '';

        $hdr = <<<HTML
          <h2 style="margin:0 0 6px 0; font-size:14px;">{$this->esc($empresa)}</h2>
          <h3 style="margin:0 0 8px 0; font-size:12px;">Reporte General de Usuarios</h3>
          <div style="font-size:9px;color:#666;">Generado: {$this->esc($fecha)}{$filtro}</div>
          <hr style="margin:8px 0 10px 0;" />
        HTML;
        $this->pdf->writeHTML($hdr, true, false, true, false, '');
        $this->pdf->writeHTML($this->table($rows), true, false, true, false, '');
        $this->pdf->Output('usuarios.pdf','I');
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function table(array $rows): string
    {
        $head = '
        <thead>
          <tr style="background-color:#F1F5F9;">
            <th width="10%"><b>ID</b></th>
            <th width="35%"><b>Nombre Completo</b></th>
            <th width="35%"><b>Email</b></th>
            <th width="10%"><b>Rol</b></th>
            <th width="10%"><b>Creado</b></th>
          </tr>
        </thead>';

        $body = '';
        if (!$rows) {
            $body = '<tr><td colspan="5" style="text-align:center;color:#666;padding:10px;">Sin resultados</td></tr>';
        } else {
            foreach ($rows as $r) {
                $roleTxt = $r['role'] === 'admin' ? 'Administrador' : 'Empleado';
                $roleColor = $r['role'] === 'admin' ? '#E53E3E' : '#3182CE';
                $roleBg = $r['role'] === 'admin' ? '#FFF5F5' : '#EBF8FF';

                $body .= sprintf(
                    '<tr>
                       <td>%s</td>
                       <td>%s</td>
                       <td>%s</td>
                       <td><span style="background:%s;color:%s;padding:2px 6px;border-radius:4px;font-size:9px;">%s</span></td>
                       <td>%s</td>
                     </tr>',
                    $this->esc((string)$r['id']),
                    $this->esc((string)$r['full_name']),
                    $this->esc((string)$r['email']),
                    $roleBg, $roleColor, $roleTxt,
                    $this->esc((string)$r['created_at'])
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