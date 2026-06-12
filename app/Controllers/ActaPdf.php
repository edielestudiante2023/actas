<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ActaModel;
use App\Models\ActaVotacionModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ActaPdf extends BaseController
{
    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ActaCompromisoModel $compromisos;
    private ActaVotacionModel $votaciones;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->compromisos = new ActaCompromisoModel();
        $this->votaciones = new ActaVotacionModel();
    }

    public function pdf(int $idActa)
    {
        $this->scope->syncActiveSession();
        $idCliente = $this->scope->activeId();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $acta = $this->actas->findForCliente($idActa, $idCliente);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        $cliente = $this->scope->active();

        $html = view('actas/pdf', [
            'cliente'     => $cliente,
            'acta'        => $acta,
            'asistentes'  => $this->asistentes->asistentesActa($idActa),
            'quorum'      => $this->asistentes->resumenQuorum($idActa),
            'compromisos' => $this->compromisos->compromisosActa($idActa),
            'votaciones'  => $this->votaciones->votacionesActa($idActa),
            'logo'        => $this->logoDataUri($cliente),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $nombre = 'acta-' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($acta['numero'] ?? $idActa)) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setBody($dompdf->output());
    }

    private function logoDataUri(?array $cliente): ?string
    {
        if ($cliente !== null && ! empty($cliente['logo'])) {
            $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $cliente['logo']);
            if (is_file($path)) {
                return $this->fileToDataUri($path);
            }
        }

        // Respaldo: marca de la app.
        $brand = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'icon-512.png';
        if (is_file($brand)) {
            return $this->fileToDataUri($brand);
        }

        return null;
    }

    private function fileToDataUri(string $path): ?string
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }

        $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'image/png') : 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
