<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaCompromisoModel;
use App\Models\ActaModel;

class ActaCompromisos extends BaseController
{
    private const ESTADOS = ['pendiente', 'en_progreso', 'cumplido', 'vencido', 'cancelado'];

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaCompromisoModel $compromisos;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->compromisos = new ActaCompromisoModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        return view('actas/compromisos', [
            'cliente'      => $this->scope->active(),
            'acta'         => $acta,
            'compromisos'  => $this->compromisos->compromisosActa($idActa),
            'responsables' => $this->compromisos->responsablesActa($idActa),
            'estados'      => self::ESTADOS,
            'editable'     => in_array($acta['estado'], ['borrador', 'en_edicion'], true),
        ]);
    }

    public function create(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/compromisos')->with('error', 'Solo puedes crear compromisos en actas editables.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload($idActa);
        if ($data['id_responsable'] === null && $data['responsable_nombre'] === null) {
            return redirect()->back()->withInput()->with('errors', ['responsable' => 'Selecciona un responsable o escribe un nombre.']);
        }

        $idCompromiso = $this->compromisos->insert($data, true);
        if (! $idCompromiso) {
            return redirect()->back()->withInput()->with('error', 'No fue posible crear el compromiso.');
        }

        $this->auditoria->registrar($idActa, 'crear_compromiso', 'Compromiso #' . $idCompromiso . ' creado.');

        return redirect()->to('/actas/' . $idActa . '/compromisos')->with('success', 'Compromiso creado correctamente.');
    }

    public function update(int $idActa, int $idCompromiso)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/compromisos')->with('error', 'Solo puedes actualizar compromisos en actas editables.');
        }

        $compromiso = $this->compromisos->findForActa($idCompromiso, $idActa);
        if ($compromiso === null) {
            return redirect()->to('/actas/' . $idActa . '/compromisos')->with('error', 'Compromiso no encontrado.');
        }

        $rules = [
            'estado' => 'required|in_list[' . implode(',', self::ESTADOS) . ']',
            'avance' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $estado = (string) $this->request->getPost('estado');
        $avance = (int) $this->request->getPost('avance');
        if ($estado === 'cumplido') {
            $avance = 100;
        }

        $this->compromisos->update($idCompromiso, [
            'estado' => $estado,
            'avance' => $avance,
        ]);
        $this->auditoria->registrar($idActa, 'actualizar_compromiso', 'Compromiso #' . $idCompromiso . ': ' . $estado . ' (' . $avance . '%).');

        return redirect()->to('/actas/' . $idActa . '/compromisos')->with('success', 'Compromiso actualizado.');
    }

    private function rules(): array
    {
        return [
            'descripcion'         => 'required|min_length[5]',
            'id_responsable'      => 'permit_empty|is_natural_no_zero',
            'responsable_nombre'  => 'permit_empty|max_length[200]',
            'fecha_vencimiento'   => 'permit_empty|valid_date[Y-m-d]',
            'estado'              => 'required|in_list[' . implode(',', self::ESTADOS) . ']',
            'avance'              => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];
    }

    private function payload(int $idActa): array
    {
        $idResponsable = (int) $this->request->getPost('id_responsable');
        $responsable = $idResponsable > 0 ? $this->responsableNombre($idActa, $idResponsable) : null;
        $estado = (string) $this->request->getPost('estado');
        $avance = (int) $this->request->getPost('avance');

        if ($estado === 'cumplido') {
            $avance = 100;
        }

        return [
            'id_acta'             => $idActa,
            'descripcion'         => trim((string) $this->request->getPost('descripcion')),
            'id_responsable'      => $responsable === null ? null : $idResponsable,
            'responsable_nombre'  => $responsable ?? $this->nullablePost('responsable_nombre'),
            'fecha_vencimiento'   => $this->nullablePost('fecha_vencimiento'),
            'estado'              => $estado,
            'avance'              => $avance,
        ];
    }

    private function responsableNombre(int $idActa, int $idUsuario): ?string
    {
        foreach ($this->compromisos->responsablesActa($idActa) as $responsable) {
            if ((int) $responsable['id_usuario'] === $idUsuario) {
                return (string) $responsable['nombre'];
            }
        }

        return null;
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
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

    private function isEditable(array $acta): bool
    {
        return in_array($acta['estado'], ['borrador', 'en_edicion'], true);
    }
}
