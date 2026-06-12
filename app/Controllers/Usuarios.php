<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\RolModel;
use App\Models\UsuarioModel;
use App\Models\UsuarioRolModel;

class Usuarios extends BaseController
{
    private UsuarioModel $usuarios;
    private RolModel $roles;
    private ClienteModel $clientes;
    private UsuarioRolModel $usuarioRoles;

    public function __construct()
    {
        $this->usuarios = new UsuarioModel();
        $this->roles = new RolModel();
        $this->clientes = new ClienteModel();
        $this->usuarioRoles = new UsuarioRolModel();
    }

    public function index()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para administrar usuarios.');
        }

        $usuarios = $this->usuarios
            ->orderBy('estado', 'ASC')
            ->orderBy('nombre_completo', 'ASC')
            ->findAll();

        $rolesPorUsuario = [];
        foreach ($this->usuarioRoles->getAsignacionesPorUsuarios(array_column($usuarios, 'id_usuario')) as $asignacion) {
            $rolesPorUsuario[(int) $asignacion['id_usuario']][] = $asignacion;
        }

        return view('usuarios/index', [
            'usuarios'        => $usuarios,
            'rolesPorUsuario' => $rolesPorUsuario,
        ]);
    }

    public function createForm()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para crear usuarios.');
        }

        return view('usuarios/form', $this->formData(null));
    }

    public function create()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para crear usuarios.');
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();
        $data['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $idUsuario = $this->usuarios->insert($data, true);
        if (! $idUsuario) {
            return redirect()->back()->withInput()->with('error', 'No fue posible crear el usuario.');
        }

        $this->usuarioRoles->syncAsignaciones((int) $idUsuario, $this->parseAsignaciones());

        return redirect()->to('/usuarios')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(int $idUsuario)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para editar usuarios.');
        }

        $usuario = $this->usuarios->find($idUsuario);
        if ($usuario === null) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        return view('usuarios/form', $this->formData($usuario));
    }

    public function update(int $idUsuario)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para editar usuarios.');
        }

        $usuario = $this->usuarios->find($idUsuario);
        if ($usuario === null) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        if (! $this->validate($this->rules(false, $idUsuario))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();
        $password = trim((string) $this->request->getPost('password'));
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->usuarios->update($idUsuario, $data);
        $this->usuarioRoles->syncAsignaciones($idUsuario, $this->parseAsignaciones());

        return redirect()->to('/usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function status(int $idUsuario)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para cambiar usuarios.');
        }

        $usuario = $this->usuarios->find($idUsuario);
        if ($usuario === null) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        $estado = (string) $this->request->getPost('estado');
        if (! in_array($estado, ['activo', 'inactivo', 'bloqueado'], true)) {
            return redirect()->to('/usuarios')->with('error', 'Estado no válido.');
        }

        $this->usuarios->update($idUsuario, [
            'estado' => $estado,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/usuarios')->with('success', 'Estado del usuario actualizado.');
    }

    private function formData(?array $usuario): array
    {
        $roles = $this->roles->activos();
        $asignaciones = [];

        if ($usuario !== null) {
            foreach ($this->usuarioRoles->getAsignaciones((int) $usuario['id_usuario']) as $asignacion) {
                $key = $asignacion['id_cliente'] === null
                    ? 'platform:' . $asignacion['id_rol']
                    : $asignacion['id_cliente'] . ':' . $asignacion['id_rol'];
                $asignaciones[$key] = true;
            }
        }

        return [
            'usuario'      => $usuario,
            'roles'        => $roles,
            'rolesCliente' => array_values(array_filter($roles, static fn ($rol) => $rol['codigo'] !== 'superadmin')),
            'rolSuperadmin' => current(array_filter($roles, static fn ($rol) => $rol['codigo'] === 'superadmin')) ?: null,
            'clientes'     => $this->clientes->where('estado', 'activo')->orderBy('nombre', 'ASC')->findAll(),
            'asignaciones' => $asignaciones,
            'action'       => $usuario === null ? base_url('usuarios') : base_url('usuarios/' . $usuario['id_usuario']),
            'isNew'        => $usuario === null,
        ];
    }

    private function rules(bool $isNew, ?int $idUsuario = null): array
    {
        $emailRule = 'required|valid_email|max_length[150]|is_unique[tbl_usuarios.email]';
        if (! $isNew && $idUsuario !== null) {
            $emailRule = 'required|valid_email|max_length[150]|is_unique[tbl_usuarios.email,id_usuario,' . $idUsuario . ']';
        }

        return [
            'nombre_completo'  => 'required|min_length[3]|max_length[200]',
            'tipo_documento'   => 'required|in_list[CC,CE,PA,NIT]',
            'numero_documento' => 'required|max_length[20]',
            'email'            => $emailRule,
            'telefono'         => 'permit_empty|max_length[20]',
            'password'         => $isNew ? 'required|min_length[8]' : 'permit_empty|min_length[8]',
            'estado'           => 'required|in_list[activo,inactivo,bloqueado]',
        ];
    }

    private function payload(): array
    {
        return [
            'nombre_completo'  => trim((string) $this->request->getPost('nombre_completo')),
            'tipo_documento'   => (string) $this->request->getPost('tipo_documento'),
            'numero_documento' => trim((string) $this->request->getPost('numero_documento')),
            'email'            => strtolower(trim((string) $this->request->getPost('email'))),
            'telefono'         => $this->nullablePost('telefono'),
            'estado'           => (string) $this->request->getPost('estado'),
        ];
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function parseAsignaciones(): array
    {
        $asignaciones = [];
        $superadmin = (int) $this->request->getPost('superadmin');
        if ($superadmin > 0) {
            $asignaciones[] = ['id_rol' => $superadmin, 'id_cliente' => null];
        }

        foreach ((array) $this->request->getPost('asignaciones') as $value) {
            $parts = explode(':', (string) $value);
            if (count($parts) !== 2) {
                continue;
            }

            $idCliente = (int) $parts[0];
            $idRol = (int) $parts[1];
            if ($idCliente <= 0 || $idRol <= 0) {
                continue;
            }

            $asignaciones[] = ['id_rol' => $idRol, 'id_cliente' => $idCliente];
        }

        return $asignaciones;
    }

    private function requireSuperadmin(): bool
    {
        return (bool) session('es_superadmin');
    }
}
