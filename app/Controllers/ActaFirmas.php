<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Libraries\EmailService;
use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaSolicitudAusenteModel;
use App\Models\ActaSolicitudReaperturaModel;
use App\Models\ActaTokenModel;
use Throwable;

class ActaFirmas extends BaseController
{
    private const DIAS_EXPIRA = 15;

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAsistenteModel $asistentes;
    private ActaSolicitudAusenteModel $solicitudesAusente;
    private ActaSolicitudReaperturaModel $solicitudesReapertura;
    private ActaTokenModel $tokens;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->solicitudesAusente = new ActaSolicitudAusenteModel();
        $this->solicitudesReapertura = new ActaSolicitudReaperturaModel();
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
            'solicitudesAusente' => $this->solicitudesAusente->solicitudesActa($idActa),
            'solicitudesReapertura' => $this->solicitudesReapertura->solicitudesActa($idActa),
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

    public function crearSolicitudReapertura(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! in_array($acta['estado'], ['pendiente_firma', 'firmada'], true)) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Solo se solicita reapertura cuando el acta está cerrada o firmada.');
        }

        $motivo = trim((string) $this->request->getPost('motivo'));
        if (mb_strlen($motivo) < 10) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'El motivo de reapertura debe tener al menos 10 caracteres.');
        }

        $this->solicitudesReapertura->insert([
            'id_acta'            => $idActa,
            'id_asistente'       => null,
            'id_cliente'         => $acta['id_cliente'],
            'solicitante_nombre' => session('nombre'),
            'solicitante_email'  => session('email'),
            'motivo'             => $motivo,
            'estado'             => 'pendiente',
            'token_hash'         => $this->solicitudesReapertura->nuevoTokenHash(),
            'expires_at'         => date('Y-m-d H:i:s', time() + 7 * 86400),
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->auditoria->registrar($idActa, 'solicitud_reapertura', 'Solicitada por: ' . (session('nombre') ?? 'Usuario') . '.');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Solicitud de reapertura registrada.');
    }

    public function aprobarSolicitudAusente(int $idActa, int $idSolicitud)
    {
        $context = $this->solicitudAusenteContext($idActa, $idSolicitud);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $solicitud = $context['solicitud'];
        $asistente = $this->asistentes
            ->where('id_acta', $idActa)
            ->where('id_asistente', $solicitud['id_asistente'])
            ->first();

        if ($asistente === null) {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'Firmante no encontrado.');
        }

        if ($asistente['firma_estado'] === 'firmada') {
            return redirect()->to('/actas/' . $idActa . '/firmas')->with('error', 'El firmante ya firmó el acta.');
        }

        $now = date('Y-m-d H:i:s');
        $this->asistentes->update($asistente['id_asistente'], [
            'asistencia'    => 'no_asiste',
            'firma_estado'  => 'ausente',
            'firma_imagen'  => null,
            'firma_ip'      => null,
            'firma_at'      => null,
        ]);
        $this->tokens->cancelarFirmaToken($idActa, (int) $asistente['id_asistente'], $this->request->getIPAddress());
        $this->solicitudesAusente->update($idSolicitud, [
            'estado'       => 'aprobada',
            'resuelta_por' => session('id_usuario'),
            'resuelta_at'  => $now,
        ]);
        $this->auditoria->registrar($idActa, 'aprobar_marcar_ausente', 'Firmante: ' . ($asistente['nombre'] ?? '') . '.');
        $this->verificarActaCompleta($idActa);

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Solicitud aprobada. El firmante quedó marcado como ausente.');
    }

    public function rechazarSolicitudAusente(int $idActa, int $idSolicitud)
    {
        $context = $this->solicitudAusenteContext($idActa, $idSolicitud);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $this->solicitudesAusente->update($idSolicitud, [
            'estado'       => 'rechazada',
            'resuelta_por' => session('id_usuario'),
            'resuelta_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->auditoria->registrar($idActa, 'rechazar_marcar_ausente', 'Solicitud #' . $idSolicitud . '.');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Solicitud rechazada.');
    }

    public function aprobarSolicitudReapertura(int $idActa, int $idSolicitud)
    {
        $context = $this->solicitudReaperturaContext($idActa, $idSolicitud);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $this->actas->update($idActa, [
            'estado'              => 'en_edicion',
            'codigo_verificacion' => null,
        ]);
        $this->solicitudesReapertura->update($idSolicitud, [
            'estado'       => 'aprobada',
            'resuelta_por' => session('id_usuario'),
            'resuelta_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->auditoria->registrar($idActa, 'aprobar_reapertura', 'Solicitud #' . $idSolicitud . '.');

        return redirect()->to('/actas/' . $idActa . '/editar')->with('success', 'Solicitud aprobada. El acta quedó en edición.');
    }

    public function rechazarSolicitudReapertura(int $idActa, int $idSolicitud)
    {
        $context = $this->solicitudReaperturaContext($idActa, $idSolicitud);
        if ($context['error'] !== null) {
            return redirect()->to($context['redirect'])->with('error', $context['error']);
        }

        $this->solicitudesReapertura->update($idSolicitud, [
            'estado'       => 'rechazada',
            'resuelta_por' => session('id_usuario'),
            'resuelta_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->auditoria->registrar($idActa, 'rechazar_reapertura', 'Solicitud #' . $idSolicitud . '.');

        return redirect()->to('/actas/' . $idActa . '/firmas')->with('success', 'Solicitud de reapertura rechazada.');
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

    private function solicitudAusenteContext(int $idActa, int $idSolicitud): array
    {
        $redirect = '/actas/' . $idActa . '/firmas';
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return ['error' => 'Acta no encontrada para el cliente activo.', 'redirect' => '/actas', 'solicitud' => null];
        }

        $solicitud = $this->solicitudesAusente
            ->where('id_solicitud', $idSolicitud)
            ->where('id_acta', $idActa)
            ->where('id_cliente', $acta['id_cliente'])
            ->first();

        if ($solicitud === null) {
            return ['error' => 'Solicitud no encontrada.', 'redirect' => $redirect, 'solicitud' => null];
        }

        if ($solicitud['estado'] !== 'pendiente') {
            return ['error' => 'La solicitud ya fue resuelta.', 'redirect' => $redirect, 'solicitud' => $solicitud];
        }

        return ['error' => null, 'redirect' => $redirect, 'solicitud' => $solicitud];
    }

    private function solicitudReaperturaContext(int $idActa, int $idSolicitud): array
    {
        $redirect = '/actas/' . $idActa . '/firmas';
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return ['error' => 'Acta no encontrada para el cliente activo.', 'redirect' => '/actas', 'solicitud' => null];
        }

        $solicitud = $this->solicitudesReapertura
            ->where('id_solicitud', $idSolicitud)
            ->where('id_acta', $idActa)
            ->where('id_cliente', $acta['id_cliente'])
            ->first();

        if ($solicitud === null) {
            return ['error' => 'Solicitud no encontrada.', 'redirect' => $redirect, 'solicitud' => null];
        }

        if ($solicitud['estado'] !== 'pendiente') {
            return ['error' => 'La solicitud ya fue resuelta.', 'redirect' => $redirect, 'solicitud' => $solicitud];
        }

        return ['error' => null, 'redirect' => $redirect, 'solicitud' => $solicitud];
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
                return;
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
