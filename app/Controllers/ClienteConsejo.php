<?php

namespace App\Controllers;

use App\Models\ClienteConsejoModel;
use App\Models\ClienteModel;
use App\Models\UsuarioRolModel;

class ClienteConsejo extends BaseController
{
    private ClienteModel $clientes;
    private ClienteConsejoModel $consejo;
    private UsuarioRolModel $usuarioRoles;

    public function __construct()
    {
        $this->clientes = new ClienteModel();
        $this->consejo = new ClienteConsejoModel();
        $this->usuarioRoles = new UsuarioRolModel();
    }

    public function index(int $idCliente)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para administrar consejos.');
        }

        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado.');
        }

        return view('clientes/consejo', [
            'cliente' => $cliente,
            'miembros' => $this->consejo->miembrosCliente($idCliente),
            'presidentesDisponibles' => $this->usuarioRoles->usuariosPorRolCliente($idCliente, 'presidente_consejo'),
            'consejerosDisponibles' => $this->usuarioRoles->usuariosPorRolCliente($idCliente, 'consejero'),
        ]);
    }

    public function create(int $idCliente)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para administrar consejos.');
        }

        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado.');
        }

        $rules = [
            'cargo' => 'required|in_list[presidente_consejo,consejero]',
            'id_usuario' => 'required|is_natural_no_zero',
            'fecha_inicio' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cargo = (string) $this->request->getPost('cargo');
        $idUsuario = (int) $this->request->getPost('id_usuario');

        if (! $this->usuarioTieneRol($idCliente, $idUsuario, $cargo)) {
            return redirect()->back()->withInput()->with('error', 'El usuario no tiene ese rol activo para este cliente.');
        }

        if ($this->consejo->existeActivo($idCliente, $idUsuario, $cargo)) {
            return redirect()->back()->withInput()->with('error', 'Ese usuario ya está activo con ese cargo en este consejo.');
        }

        if ($cargo === 'presidente_consejo') {
            $this->consejo->cerrarPresidenteActivo($idCliente);
        }

        $this->consejo->insert([
            'id_cliente' => $idCliente,
            'id_usuario' => $idUsuario,
            'cargo' => $cargo,
            'estado' => 'activo',
            'fecha_inicio' => $this->nullablePost('fecha_inicio'),
            'fecha_fin' => null,
        ]);

        return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('success', 'Miembro agregado al consejo.');
    }

    public function status(int $idCliente, int $id)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para administrar consejos.');
        }

        $miembro = $this->consejo
            ->where('id_cliente', $idCliente)
            ->find($id);

        if ($miembro === null) {
            return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('error', 'Miembro no encontrado.');
        }

        $estado = (string) $this->request->getPost('estado');
        if (! in_array($estado, ['activo', 'inactivo'], true)) {
            return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('error', 'Estado no válido.');
        }

        if ($estado === 'activo') {
            if (! $this->usuarioTieneRol($idCliente, (int) $miembro['id_usuario'], $miembro['cargo'])) {
                return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('error', 'El usuario ya no tiene ese rol activo para este cliente.');
            }

            if ($miembro['cargo'] === 'presidente_consejo') {
                $this->consejo->cerrarPresidenteActivo($idCliente);
            } elseif ($this->consejo->existeActivo($idCliente, (int) $miembro['id_usuario'], $miembro['cargo'])) {
                return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('error', 'Ese usuario ya está activo con ese cargo en este consejo.');
            }
        }

        $this->consejo->update($id, [
            'estado' => $estado,
            'fecha_fin' => $estado === 'inactivo' ? date('Y-m-d') : null,
        ]);

        return redirect()->to('/clientes/' . $idCliente . '/consejo')->with('success', 'Estado del miembro actualizado.');
    }

    private function usuarioTieneRol(int $idCliente, int $idUsuario, string $cargo): bool
    {
        $usuarios = $this->usuarioRoles->usuariosPorRolCliente($idCliente, $cargo);
        foreach ($usuarios as $usuario) {
            if ((int) $usuario['id_usuario'] === $idUsuario) {
                return true;
            }
        }

        return false;
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function requireSuperadmin(): bool
    {
        return (bool) session('es_superadmin');
    }
}
