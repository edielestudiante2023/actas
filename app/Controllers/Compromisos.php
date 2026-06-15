<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaCompromisoModel;

class Compromisos extends BaseController
{
    private const ESTADOS = ['pendiente', 'en_progreso', 'cumplido', 'vencido', 'cancelado'];

    private ClienteScope $scope;
    private ActaCompromisoModel $compromisos;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->compromisos = new ActaCompromisoModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index()
    {
        $idCliente = $this->requireCliente();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $todos     = $this->compromisos->compromisosCliente($idCliente);
        $idUsuario = (int) session('id_usuario');

        // "Mios" = compromisos cuyo responsable soy yo.
        $mios = array_values(array_filter($todos, static fn ($c) => (int) ($c['id_responsable'] ?? 0) === $idUsuario));

        // Default del filtro segun rol: admin/superadmin ven "todos"; el resto, "mios".
        $esAdmin = (bool) session('es_superadmin') || in_array('administrador', (array) session('roles'), true);
        $param   = $this->request->getGet('mios');
        $verMios = $param === null ? ! $esAdmin : ($param === '1');

        $lista = $verMios ? $mios : $todos;

        $resumen = ['total' => count($lista), 'pendiente' => 0, 'en_progreso' => 0, 'cumplido' => 0, 'vencido' => 0, 'cancelado' => 0];
        foreach ($lista as $c) {
            $estado = (string) $c['estado'];
            if (isset($resumen[$estado])) {
                $resumen[$estado]++;
            }
        }

        return view('compromisos/index', [
            'cliente'     => $this->scope->active(),
            'compromisos' => $lista,
            'estados'     => self::ESTADOS,
            'resumen'     => $resumen,
            'verMios'     => $verMios,
            'countTodos'  => count($todos),
            'countMios'   => count($mios),
        ]);
    }

    public function update(int $idCompromiso)
    {
        $idCliente = $this->requireCliente();
        if ($idCliente === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        $compromiso = $this->compromisos->findForCliente($idCompromiso, $idCliente);
        if ($compromiso === null) {
            return redirect()->to('/compromisos')->with('error', 'Compromiso no encontrado.');
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

        // Las tareas siguen vivas aunque el acta esté cerrada/firmada.
        $this->compromisos->update($idCompromiso, ['estado' => $estado, 'avance' => $avance]);
        $this->auditoria->registrar((int) $compromiso['id_acta'], 'actualizar_compromiso', 'Compromiso #' . $idCompromiso . ' (modulo): ' . $estado . ' (' . $avance . '%).');

        return redirect()->to('/compromisos')->with('success', 'Compromiso actualizado.');
    }

    private function requireCliente(): ?int
    {
        $this->scope->syncActiveSession();

        return $this->scope->activeId();
    }
}
