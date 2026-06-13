<?php

namespace App\Controllers;

use App\Libraries\EmailService;
use App\Models\PasswordResetModel;
use App\Models\UsuarioModel;
use Throwable;

class PasswordReset extends BaseController
{
    private UsuarioModel $usuarios;
    private PasswordResetModel $resets;

    public function __construct()
    {
        $this->usuarios = new UsuarioModel();
        $this->resets = new PasswordResetModel();
    }

    public function requestForm()
    {
        return view('auth/forgot');
    }

    public function send()
    {
        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Ingresa un correo válido.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $usuario = $this->usuarios->findByEmail($email);

        if ($usuario === null || $usuario['estado'] !== 'activo') {
            return redirect()->to('/login')->with('success', 'Si el correo existe, enviaremos instrucciones para recuperar la contraseña.');
        }

        $token = $this->resets->crear((int) $usuario['id_usuario'], $this->request->getIPAddress());
        $resetUrl = base_url('password/reset/' . $token);
        $html = view('emails/password_reset', [
            'usuario'  => $usuario,
            'resetUrl' => $resetUrl,
            'expira'   => date('d/m/Y H:i', time() + 3600),
        ]);

        try {
            (new EmailService())->sendHtml($email, (string) $usuario['nombre_completo'], 'Recuperar contraseña - Actas', $html);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to('/login')->with('success', 'Si el correo existe, enviaremos instrucciones para recuperar la contraseña.');
    }

    public function resetForm(string $token)
    {
        $reset = $this->resets->findValid($token);
        if ($reset === null) {
            return view('auth/reset_invalid');
        }

        return view('auth/reset', ['token' => $token]);
    }

    public function update(string $token)
    {
        $reset = $this->resets->findValid($token);
        if ($reset === null) {
            return view('auth/reset_invalid');
        }

        $rules = [
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->usuarios->update($reset['id_usuario'], [
            'password'   => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->resets->marcarUsado((int) $reset['id_reset']);

        return redirect()->to('/login')->with('success', 'Contraseña actualizada. Ya puedes ingresar.');
    }
}
