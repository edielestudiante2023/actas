<?php

namespace App\Filters;

use App\Libraries\ClienteScope;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ClienteFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Debes iniciar sesión para continuar.');
        }

        $scope = new ClienteScope();
        $scope->syncActiveSession();

        if ($scope->activeId() === null) {
            return redirect()->to('/dashboard')->with('error', 'Selecciona un cliente para continuar.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // sin acción
    }
}
