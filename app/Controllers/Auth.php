<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Ingresa un correo y contraseña válidos.');
        }

        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $model = new UsuarioModel();
        $user  = $model->findByEmail($email);

        if ($user === null || $user['estado'] !== 'activo' || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Credenciales incorrectas o usuario inactivo.');
        }

        $roles    = $model->getRoles((int) $user['id_usuario']);
        $codigos  = array_column($roles, 'codigo');

        session()->set([
            'isLoggedIn'    => true,
            'id_usuario'    => (int) $user['id_usuario'],
            'nombre'        => $user['nombre_completo'],
            'email'         => $user['email'],
            'roles'         => $codigos,
            'roles_full'    => $roles,
            'es_superadmin' => in_array('superadmin', $codigos, true),
        ]);

        $model->update($user['id_usuario'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);
        (new ClienteScope())->syncActiveSession();

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Sesión cerrada correctamente.');
    }
}
