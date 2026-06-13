<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaTokenModel;

class ActaFirmas extends BaseController
{
    private const DIAS_EXPIRA = 15;

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ActaTokenModel $tokens;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->tokens = new ActaTokenModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function estado(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        $asistentes = $this->asistentes->asistentesActa($idActa);
        $mapTokens  = $this->tokens->firmaTokensPorAsistente($idActa);

        $firmantes = array_values(array_filter($asistentes, static fn ($a) => (int) $a['requiere_firma'] === 1 && $a['asistencia'] === 'asiste'));
        $firmados  = count(array_filter($firmantes, static fn ($a) => $a['firma_estado'] === 'firmada'));

        return view('actas/firmas', [
            'cliente'    => $this->scope->active(),
            'acta'       => $acta,
            'asistentes' => $asistentes,
            'tokens'     => $mapTokens,
            'total'      => count($firmantes),
            'firmados'   => $firmados,
            'editable'   => in_array($acta['estado'], ['borrador', 'en_edicion'], true),
        ]);
    }

    public function cerrar(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! in_array($acta['estado'], ['borrador', 'en_edicion'], true)) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'El acta no está en estado editable.');
        }

        $asistentes = $this->asistentes->asistentesActa($idActa);
        $firmantes  = array_values(array_filter($asistentes, static fn ($a) => (int) $a['requiere_firma'] === 1 && $a['asistencia'] === 'asiste'));

        if ($firmantes === []) {
            return redirect()->to('/actas/' . $idActa . '/editar')->with('error', 'No hay firmantes (asistentes que asisten y requieren firma). Revisa los asistentes.');
        }

        // Marca estados de firma de cada asistente.
        foreach ($asistentes as $a) {
            $esFirmante = (int) $a['requiere_firma'] === 1 && $a['asistencia'] === 'asiste';
            $this->asistentes->update($a['id_asistente'], [
                'firma_estado' => $esFirmante ? 'pendiente' : 'no_requerida',
            ]);
        }

        // Regenera tokens de firma.
        $this->tokens->eliminarFirmaTokens($idActa);
        $now     = date('Y-m-d H:i:s');
        $expira  = date('Y-m-d H:i:s', time() + self::DIAS_EXPIRA * 86400);
        foreach ($firmantes as $f) {
            $this->tokens->insert([
                'token'        => $this->tokens->nuevoToken(),
                'tipo'         => 'firmar_acta',
                'id_acta'      => $idActa,
                'id_asistente' => $f['id_asistente'],
                'id_cliente'   => $acta['id_cliente'],
                'expires_at'   => $expira,
                'usado_at'     => null,
                'ip_uso'       => null,
                'created_at'   => $now,
            ]);
        }

        $this->actas->update($idActa, ['estado' => 'pendiente_firma']);
        $this->auditoria->registrar($idActa, 'cerrar_enviar_firmas', 'Acta cerrada y enviada a firmas (' . count($firmantes) . ' firmantes).');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Acta cerrada. Se generaron los enlaces de firma.');
    }

    private function actaContext(int $idActa): ?array
    {
        $this->scope->syncActiveSession();
        $idCliente = $this->scope->activeId();
        if ($idCliente === null) {
            return null;
        }

        return $this->actas->findForCliente($idActa, $idCliente);
    }
}
