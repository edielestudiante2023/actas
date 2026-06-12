<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;

class ActaAsistentes extends BaseController
{
    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index(int $idActa)
    {
        $context = $this->actaContext($idActa);
        if ($context === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        return view('actas/asistentes', [
            'cliente'    => $this->scope->active(),
            'acta'       => $context,
            'asistentes' => $this->asistentes->asistentesActa($idActa),
            'quorum'     => $this->asistentes->resumenQuorum($idActa),
        ]);
    }

    public function importarConsejo(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! in_array($acta['estado'], ['borrador', 'en_edicion'], true)) {
            return redirect()->to('/actas/' . $idActa . '/asistentes')->with('error', 'Solo puedes modificar asistentes en actas editables.');
        }

        $insertados = $this->asistentes->importarConsejo($idActa, (int) $acta['id_cliente']);
        $this->auditoria->registrar($idActa, 'importar_asistentes_consejo', 'Miembros importados: ' . $insertados);

        return redirect()->to('/actas/' . $idActa . '/asistentes')->with('success', 'Miembros importados: ' . $insertados);
    }

    public function update(int $idActa, int $idAsistente)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! in_array($acta['estado'], ['borrador', 'en_edicion'], true)) {
            return redirect()->to('/actas/' . $idActa . '/asistentes')->with('error', 'Solo puedes modificar asistentes en actas editables.');
        }

        $asistente = $this->asistentes
            ->where('id_acta', $idActa)
            ->find($idAsistente);

        if ($asistente === null) {
            return redirect()->to('/actas/' . $idActa . '/asistentes')->with('error', 'Asistente no encontrado.');
        }

        $rules = [
            'asistencia' => 'required|in_list[asiste,no_asiste,excusa]',
            'requiere_firma' => 'permit_empty|in_list[1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $asistencia = (string) $this->request->getPost('asistencia');
        $requiereFirma = $this->request->getPost('requiere_firma') === '1' ? 1 : 0;
        $firmaEstado = 'no_requerida';

        if ($requiereFirma === 1) {
            $firmaEstado = $asistencia === 'asiste' ? 'pendiente' : 'ausente';
            if (($asistente['firma_estado'] ?? '') === 'firmada') {
                $firmaEstado = 'firmada';
            }
        }

        $this->asistentes->update($idAsistente, [
            'asistencia'     => $asistencia,
            'requiere_firma' => $requiereFirma,
            'firma_estado'   => $firmaEstado,
        ]);
        $this->auditoria->registrar($idActa, 'actualizar_asistencia', 'Asistente #' . $idAsistente . ': ' . $asistencia);

        return redirect()->to('/actas/' . $idActa . '/asistentes')->with('success', 'Asistencia actualizada.');
    }

    private function actaContext(int $idActa): ?array
    {
        $this->scope->syncActiveSession();
        $idCliente = $this->scope->activeId();
        if ($idCliente === null) {
            return null;
        }

        return $this->actas->findForCliente($idActa, $idCliente);
    }
}
