<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Debes iniciar sesión para continuar.');
        }

        if ((bool) session('es_superadmin')) {
            return null;
        }

        $required = array_filter((array) $arguments);
        if ($required === []) {
            return null;
        }

        $roles = (array) session('roles');
        if (array_intersect($required, $roles) !== []) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // sin acción
    }
}
