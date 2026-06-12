<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;

class Actas extends BaseController
{
    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index()
    {
        $idCliente = $this->requireClienteActivo();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $actas = $this->actas
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha', 'DESC')
            ->orderBy('consecutivo', 'DESC')
            ->findAll();

        return view('actas/index', [
            'cliente' => $this->scope->active(),
            'actas'   => $actas,
        ]);
    }

    public function createForm()
    {
        $idCliente = $this->requireClienteActivo();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        return view('actas/form', [
            'cliente' => $this->scope->active(),
            'acta'    => null,
            'action'  => base_url('actas'),
            'isNew'   => true,
        ]);
    }

    public function create()
    {
        $idCliente = $this->requireClienteActivo();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fecha = (string) $this->request->getPost('fecha');
        $consecutivo = $this->actas->nextConsecutivo($idCliente);
        $numero = $this->actas->buildNumero($idCliente, $consecutivo, $fecha);

        $data = $this->payload();
        $data['id_cliente'] = $idCliente;
        $data['consecutivo'] = $consecutivo;
        $data['numero'] = $numero;
        $data['estado'] = 'borrador';
        $data['creada_por'] = session('id_usuario');

        $idActa = $this->actas->insert($data, true);
        if (! $idActa) {
            return redirect()->back()->withInput()->with('error', 'No fue posible crear el acta.');
        }

        $this->auditoria->registrar((int) $idActa, 'crear_borrador', 'Acta creada como borrador.');

        return redirect()->to('/actas/' . $idActa . '/editar')->with('success', 'Acta borrador creada correctamente.');
    }

    public function edit(int $idActa)
    {
        $idCliente = $this->requireClienteActivo();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $acta = $this->actas->findForCliente($idActa, $idCliente);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        return view('actas/form', [
            'cliente' => $this->scope->active(),
            'acta'    => $acta,
            'action'  => base_url('actas/' . $idActa),
            'isNew'   => false,
        ]);
    }

    public function update(int $idActa)
    {
        $idCliente = $this->requireClienteActivo();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $acta = $this->actas->findForCliente($idActa, $idCliente);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! in_array($acta['estado'], ['borrador', 'en_edicion'], true)) {
            return redirect()->to('/actas')->with('error', 'Solo puedes editar actas en borrador o en edición.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();
        if ($acta['fecha'] !== $data['fecha']) {
            $data['numero'] = $this->actas->buildNumero($idCliente, (int) $acta['consecutivo'], $data['fecha']);
        }

        $this->actas->update($idActa, $data);
        $this->auditoria->registrar($idActa, 'actualizar_borrador', 'Acta actualizada en estado ' . $acta['estado'] . '.');

        return redirect()->to('/actas')->with('success', 'Acta actualizada correctamente.');
    }

    private function rules(): array
    {
        return [
            'titulo'      => 'permit_empty|max_length[200]',
            'fecha'       => 'required|valid_date[Y-m-d]',
            'hora_inicio' => 'permit_empty|regex_match[/^([01][0-9]|2[0-3]):[0-5][0-9]$/]',
            'hora_fin'    => 'permit_empty|regex_match[/^([01][0-9]|2[0-3]):[0-5][0-9]$/]',
            'lugar'       => 'permit_empty|max_length[200]',
            'modalidad'   => 'required|in_list[presencial,virtual,mixta]',
            'objeto'      => 'permit_empty',
            'orden_dia'   => 'permit_empty',
            'desarrollo'  => 'permit_empty',
            'observaciones' => 'permit_empty',
        ];
    }

    private function payload(): array
    {
        return [
            'titulo'       => $this->nullablePost('titulo'),
            'fecha'        => (string) $this->request->getPost('fecha'),
            'hora_inicio'  => $this->nullablePost('hora_inicio'),
            'hora_fin'     => $this->nullablePost('hora_fin'),
            'lugar'        => $this->nullablePost('lugar'),
            'modalidad'    => (string) $this->request->getPost('modalidad'),
            'objeto'       => $this->nullablePost('objeto'),
            'orden_dia'    => $this->nullablePost('orden_dia'),
            'desarrollo'   => $this->nullablePost('desarrollo'),
            'observaciones' => $this->nullablePost('observaciones'),
        ];
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function requireClienteActivo(): ?int
    {
        $this->scope->syncActiveSession();

        return $this->scope->activeId();
    }
}
