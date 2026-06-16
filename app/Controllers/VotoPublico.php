<?php

namespace App\Controllers;

use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaVotacionModel;
use App\Models\ActaVotacionTokenModel;
use App\Models\ActaVotacionVotoModel;
use App\Models\ClienteModel;

class VotoPublico extends BaseController
{
    private const VOTOS = ['favor', 'contra', 'abstencion'];

    private ActaVotacionTokenModel $tokens;
    private ActaVotacionModel $votaciones;
    private ActaVotacionVotoModel $votos;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ClienteModel $clientes;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->tokens = new ActaVotacionTokenModel();
        $this->votaciones = new ActaVotacionModel();
        $this->votos = new ActaVotacionVotoModel();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->clientes = new ClienteModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function votar(string $token)
    {
        $reg = $this->tokens->findUsable($token);
        if ($reg === null) {
            return view('votos/publico/invalido');
        }

        $votacion = $this->votaciones->find($reg['id_votacion']);
        if ($votacion === null || $votacion['estado'] !== 'abierta') {
            return view('votos/publico/invalido', ['cerrada' => true]);
        }

        return view('votos/publico/votar', [
            'token'     => $token,
            'votacion'  => $votacion,
            'acta'      => $this->actas->find($votacion['id_acta']),
            'asistente' => $this->asistentes->find($reg['id_asistente']),
            'cliente'   => $this->clientes->find($reg['id_cliente']),
            'miVoto'    => $this->votos->miVoto((int) $reg['id_votacion'], (int) $reg['id_asistente']),
        ]);
    }

    public function procesarVoto(string $token)
    {
        $reg = $this->tokens->findUsable($token);
        if ($reg === null) {
            return redirect()->to('/votar/' . $token);
        }

        $votacion = $this->votaciones->find($reg['id_votacion']);
        if ($votacion === null || $votacion['estado'] !== 'abierta') {
            return redirect()->to('/votar/' . $token);
        }

        $voto = (string) $this->request->getPost('voto');
        if (! in_array($voto, self::VOTOS, true)) {
            return redirect()->to('/votar/' . $token)->with('error', 'Opción de voto inválida.');
        }

        $this->votos->registrar((int) $reg['id_votacion'], (int) $reg['id_asistente'], $voto, (string) $this->request->getIPAddress());
        $this->tokens->update($reg['id'], ['usado_at' => date('Y-m-d H:i:s'), 'ip_uso' => $this->request->getIPAddress()]);
        $this->auditoria->registrar((int) $votacion['id_acta'], 'voto_email', 'Voto por enlace registrado.');

        return redirect()->to('/voto-exitoso')->with('voto_ok', true);
    }

    public function exitoso()
    {
        if (! session('voto_ok')) {
            return redirect()->to('/login');
        }

        return view('votos/publico/ok');
    }
}
