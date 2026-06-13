<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Libraries\EmailService;
use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaTokenModel;
use Throwable;

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

    public function enviarEmailTodos(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if ($acta['estado'] !== 'pendiente_firma') {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Solo se envían correos cuando el acta está pendiente de firma.');
        }

        $asistentes = $this->asistentes->asistentesActa($idActa);
        $tokens = $this->tokens->firmaTokensPorAsistente($idActa);
        $enviados = 0;
        $omitidos = 0;
        $errores = [];

        foreach ($asistentes as $asistente) {
            if (! $this->puedeRecibirEmail($asistente, $tokens)) {
                $omitidos++;
                continue;
            }

            try {
                $this->enviarEmailFirma($acta, $asistente, $tokens[(int) $asistente['id_asistente']]);
                $enviados++;
            } catch (Throwable $e) {
                $errores[] = $asistente['nombre'] . ': ' . $e->getMessage();
            }
        }

        if ($enviados > 0) {
            $this->auditoria->registrar($idActa, 'enviar_firmas_email', 'Correos enviados: ' . $enviados . '. Omitidos: ' . $omitidos . '.');
        }

        if ($errores !== []) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Enviados: ' . $enviados . '. Errores: ' . implode(' | ', array_slice($errores, 0, 3)));
        }

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Correos enviados: ' . $enviados . '. Omitidos: ' . $omitidos . '.');
    }

    public function enviarEmailIndividual(int $idActa, int $idAsistente)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if ($acta['estado'] !== 'pendiente_firma') {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Solo se envían correos cuando el acta está pendiente de firma.');
        }

        $asistente = $this->asistentes
            ->where('id_acta', $idActa)
            ->where('id_asistente', $idAsistente)
            ->first();

        if ($asistente === null) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Firmante no encontrado.');
        }

        $tokens = $this->tokens->firmaTokensPorAsistente($idActa);
        if (! $this->puedeRecibirEmail($asistente, $tokens)) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'El firmante no tiene correo válido, token vigente o ya firmó.');
        }

        try {
            $this->enviarEmailFirma($acta, $asistente, $tokens[$idAsistente]);
        } catch (Throwable $e) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', $e->getMessage());
        }

        $this->auditoria->registrar($idActa, 'enviar_firma_email', 'Correo enviado a: ' . ($asistente['email'] ?? '') . '.');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Correo enviado a ' . $asistente['nombre'] . '.');
    }

    public function regenerarEnlace(int $idActa, int $idAsistente)
    {
        $context = $this->firmanteContext($idActa, $idAsistente);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $acta = $context['acta'];
        $asistente = $context['asistente'];
        $token = $this->tokens->regenerarFirmaToken($idActa, $idAsistente, (int) $acta['id_cliente'], self::DIAS_EXPIRA);
        $this->auditoria->registrar($idActa, 'regenerar_enlace_firma', 'Firmante: ' . ($asistente['nombre'] ?? '') . '.');

        if ($this->request->getPost('enviar_email') === '1' && ! empty($asistente['email'])) {
            try {
                $this->enviarEmailFirma($acta, $asistente, $token);
                $this->auditoria->registrar($idActa, 'reenviar_firma_email', 'Correo reenviado a: ' . ($asistente['email'] ?? '') . '.');

                return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Enlace regenerado y enviado por email a ' . $asistente['nombre'] . '.');
            } catch (Throwable $e) {
                return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'El enlace se regeneró, pero no se pudo enviar email: ' . $e->getMessage());
            }
        }

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Enlace regenerado para ' . $asistente['nombre'] . '.');
    }

    public function cancelarEnlace(int $idActa, int $idAsistente)
    {
        $context = $this->firmanteContext($idActa, $idAsistente);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $asistente = $context['asistente'];
        if (! $this->tokens->cancelarFirmaToken($idActa, $idAsistente, $this->request->getIPAddress())) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'No hay enlace vigente para cancelar.');
        }

        $this->auditoria->registrar($idActa, 'cancelar_enlace_firma', 'Firmante: ' . ($asistente['nombre'] ?? '') . '.');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Enlace cancelado para ' . $asistente['nombre'] . '.');
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

    private function firmanteContext(int $idActa, int $idAsistente): array
    {
        $redirect = '/actas/' . $idActa . '/firmas';
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return ['error' => 'Acta no encontrada para el cliente activo.', 'redirect' => '/actas', 'acta' => null, 'asistente' => null];
        }

        if ($acta['estado'] !== 'pendiente_firma') {
            return ['error' => 'Solo puedes administrar enlaces cuando el acta está pendiente de firma.', 'redirect' => $redirect, 'acta' => $acta, 'asistente' => null];
        }

        $asistente = $this->asistentes
            ->where('id_acta', $idActa)
            ->where('id_asistente', $idAsistente)
            ->first();

        if ($asistente === null) {
            return ['error' => 'Firmante no encontrado.', 'redirect' => $redirect, 'acta' => $acta, 'asistente' => null];
        }

        if ((int) $asistente['requiere_firma'] !== 1 || $asistente['asistencia'] !== 'asiste') {
            return ['error' => 'El asistente no requiere firma activa.', 'redirect' => $redirect, 'acta' => $acta, 'asistente' => $asistente];
        }

        if ($asistente['firma_estado'] === 'firmada') {
            return ['error' => 'El firmante ya firmó el acta.', 'redirect' => $redirect, 'acta' => $acta, 'asistente' => $asistente];
        }

        return ['error' => null, 'redirect' => $redirect, 'acta' => $acta, 'asistente' => $asistente];
    }

    private function puedeRecibirEmail(array $asistente, array $tokens): bool
    {
        $idAsistente = (int) $asistente['id_asistente'];
        $token = $tokens[$idAsistente] ?? null;

        return (int) $asistente['requiere_firma'] === 1
            && $asistente['asistencia'] === 'asiste'
            && $asistente['firma_estado'] !== 'firmada'
            && $token !== null
            && empty($token['usado_at'])
            && ! empty($asistente['email'])
            && filter_var((string) $asistente['email'], FILTER_VALIDATE_EMAIL) !== false;
    }

    private function enviarEmailFirma(array $acta, array $asistente, array $token): void
    {
        $cliente = $this->scope->active();
        $firmaUrl = base_url('firmar/' . $token['token']);
        $html = view('emails/firma_enlace', [
            'nombre'   => $asistente['nombre'] ?? '',
            'cliente'  => $cliente,
            'acta'     => $acta,
            'firmaUrl' => $firmaUrl,
            'expira'   => ! empty($token['expires_at']) ? date('d/m/Y H:i', strtotime((string) $token['expires_at'])) : null,
        ]);

        $subject = 'Firma pendiente - Acta ' . ($acta['numero'] ?? $acta['id_acta']);
        (new EmailService())->sendHtml((string) $asistente['email'], (string) $asistente['nombre'], $subject, $html);
    }
}
