<?php

namespace App\Controllers;

use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaTokenModel;
use App\Models\ClienteModel;

class ActaFirmaPublica extends BaseController
{
    private ActaTokenModel $tokens;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ClienteModel $clientes;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->tokens = new ActaTokenModel();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->clientes = new ClienteModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function firmar(string $token)
    {
        $registro = $this->tokens->findValid($token);
        if ($registro === null) {
            return view('firmas/publico/invalido');
        }

        $asistente = $this->asistentes->find($registro['id_asistente']);
        if ($asistente === null || $asistente['firma_estado'] === 'firmada') {
            return view('firmas/publico/invalido', ['yaFirmado' => $asistente !== null]);
        }

        $acta    = $this->actas->find($registro['id_acta']);
        $cliente = $this->clientes->find($registro['id_cliente']);

        return view('firmas/publico/firmar', [
            'token'     => $token,
            'acta'      => $acta,
            'asistente' => $asistente,
            'cliente'   => $cliente,
        ]);
    }

    public function procesarFirma(string $token)
    {
        $registro = $this->tokens->findValid($token);
        if ($registro === null) {
            return redirect()->to('/firmar/' . $token);
        }

        $asistente = $this->asistentes->find($registro['id_asistente']);
        if ($asistente === null || $asistente['firma_estado'] === 'firmada') {
            return redirect()->to('/firmar/' . $token);
        }

        $firma = (string) $this->request->getPost('firma_imagen');
        if (! preg_match('/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/', $firma)) {
            return redirect()->to('/firmar/' . $token)->with('error', 'La firma es obligatoria. Dibuja tu firma antes de enviar.');
        }

        $now = date('Y-m-d H:i:s');
        $this->asistentes->update($registro['id_asistente'], [
            'firma_estado' => 'firmada',
            'firma_imagen' => $firma,
            'firma_ip'     => $this->request->getIPAddress(),
            'firma_at'     => $now,
        ]);

        $this->tokens->update($registro['id_token'], [
            'usado_at' => $now,
            'ip_uso'   => $this->request->getIPAddress(),
        ]);

        $this->auditoria->registrar((int) $registro['id_acta'], 'firma_registrada', 'Firmó: ' . ($asistente['nombre'] ?? '') . '.');

        $this->verificarActaCompleta((int) $registro['id_acta']);

        return redirect()->to('/firma-exitosa')->with('firma_ok', true);
    }

    public function exitosa()
    {
        if (! session('firma_ok')) {
            return redirect()->to('/login');
        }

        return view('firmas/publico/ok');
    }

    private function verificarActaCompleta(int $idActa): void
    {
        $firmantes = $this->asistentes
            ->where('id_acta', $idActa)
            ->where('requiere_firma', 1)
            ->where('asistencia', 'asiste')
            ->findAll();

        if ($firmantes === []) {
            return;
        }

        foreach ($firmantes as $f) {
            if ($f['firma_estado'] !== 'firmada') {
                return; // aún faltan firmas
            }
        }

        $acta = $this->actas->find($idActa);
        if ($acta !== null && $acta['estado'] !== 'firmada') {
            $codigo = strtoupper(substr(hash('sha256', $idActa . '|' . $acta['numero'] . '|' . date('YmdHis')), 0, 12));
            $this->actas->update($idActa, [
                'estado'              => 'firmada',
                'codigo_verificacion' => $codigo,
            ]);
            $this->auditoria->registrar($idActa, 'acta_firmada', 'Todas las firmas completadas. Código: ' . $codigo . '.');
        }
    }
}
