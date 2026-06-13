<?php

namespace App\Controllers;

use App\Models\ActaAsistenteModel;
use App\Models\ActaModel;
use App\Models\ClienteModel;

class ActaVerificacion extends BaseController
{
    private ActaModel $actas;
    private ClienteModel $clientes;
    private ActaAsistenteModel $asistentes;

    public function __construct()
    {
        $this->actas = new ActaModel();
        $this->clientes = new ClienteModel();
        $this->asistentes = new ActaAsistenteModel();
    }

    public function form()
    {
        return view('firmas/publico/verificar', [
            'codigo' => '',
            'acta'   => null,
        ]);
    }

    public function buscar()
    {
        $codigo = $this->normalizarCodigo((string) $this->request->getPost('codigo'));
        if ($codigo === '') {
            return redirect()->to('/verificar')->with('error', 'Ingresa el código de verificación.');
        }

        return redirect()->to('/verificar/' . rawurlencode($codigo));
    }

    public function resultado(string $codigo)
    {
        $codigo = $this->normalizarCodigo($codigo);
        if ($codigo === '') {
            return redirect()->to('/verificar')->with('error', 'Ingresa el código de verificación.');
        }

        $acta = $this->actas->findFirmadaPorCodigo($codigo);

        if ($acta === null) {
            return view('firmas/publico/verificar', [
                'codigo' => $codigo,
                'acta'   => null,
                'error'  => 'No encontramos un acta firmada con ese código.',
            ]);
        }

        $cliente = $this->clientes->find($acta['id_cliente']);
        $firmantes = $this->asistentes
            ->where('id_acta', $acta['id_acta'])
            ->where('requiere_firma', 1)
            ->where('asistencia', 'asiste')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return view('firmas/publico/verificar', [
            'codigo'    => $codigo,
            'acta'      => $acta,
            'cliente'   => $cliente,
            'firmantes' => $firmantes,
        ]);
    }

    private function normalizarCodigo(string $codigo): string
    {
        $codigo = strtoupper(trim($codigo));
        $codigo = preg_replace('/[^A-Z0-9-]/', '', $codigo) ?? '';

        return substr($codigo, 0, 80);
    }
}
