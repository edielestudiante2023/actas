<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAnexoModel;
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
    private ActaAnexoModel $anexos;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->compromisos = new ActaCompromisoModel();
        $this->votaciones = new ActaVotacionModel();
        $this->anexos = new ActaAnexoModel();
    }

    public function pdf(int $idActa)
    {
        $data = $this->viewData($idActa);
        if ($data === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        // Dompdf puede requerir bastante memoria; ampliamos solo para esta operación.
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        $html = view('actas/pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 96);
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $nombre = $this->fileName($data['acta'], $idActa, 'pdf');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setBody($dompdf->output());
    }

    public function word(int $idActa)
    {
        $data = $this->viewData($idActa);
        if ($data === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        $html = "\xEF\xBB\xBF" . view('actas/pdf', $data);
        $nombre = $this->fileName($data['acta'], $idActa, 'doc');

        return $this->response
            ->setHeader('Content-Type', 'application/msword; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombre . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setBody($html);
    }

    private function viewData(int $idActa): ?array
    {
        $this->scope->syncActiveSession();
        $idCliente = $this->scope->activeId();
        if ($idCliente === null) {
            return null;
        }

        $acta = $this->actas->findForCliente($idActa, $idCliente);
        if ($acta === null) {
            return null;
        }

        $cliente = $this->scope->active();

        return [
            'cliente'     => $cliente,
            'acta'        => $acta,
            'asistentes'  => $this->asistentes->asistentesActa($idActa),
            'quorum'      => $this->asistentes->resumenQuorum($idActa),
            'compromisos' => $this->compromisos->compromisosActa($idActa),
            'votaciones'  => $this->votaciones->votacionesActa($idActa),
            'anexos'      => $this->anexos->anexosActa($idActa),
            'logo'        => $this->logoDataUri($cliente),
        ];
    }

    private function fileName(array $acta, int $idActa, string $extension): string
    {
        $numero = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($acta['numero'] ?? $idActa));

        return 'acta-' . $numero . '.' . $extension;
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

        return 'data:' . $this->mimePorExtension($path) . ';base64,' . base64_encode($data);
    }

    private function mimePorExtension(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            'gif'         => 'image/gif',
            default       => 'image/png',
        };
    }
}
