<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;

class Dashboard extends BaseController
{
    public function index()
    {
        $scope = new ClienteScope();
        $scope->syncActiveSession();

        return view('dashboard/index', [
            'clientes_disponibles' => $scope->availableForCurrentUser(),
            'cliente_activo'       => $scope->active(),
        ]);
    }
}
