<?php

namespace App\Libraries;

use Config\Database;

/**
 * Calcula los "pendientes" de acción del usuario en sesión (para la campana del PWA).
 * Todo se calcula al vuelo; no usa tabla de notificaciones.
 */
class Pendientes
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /** Votaciones digitales abiertas donde el usuario es asistente presente y aún no vota. */
    public function votaciones(int $idUsuario): array
    {
        return $this->db->table('tbl_acta_votaciones v')
            ->select('v.id_votacion, v.titulo, v.id_acta, a.numero AS acta_numero')
            ->join('tbl_actas a', 'a.id_acta = v.id_acta')
            ->join('tbl_acta_asistentes asi', 'asi.id_acta = v.id_acta')
            ->where('v.estado', 'abierta')
            ->where('v.modo', 'digital')
            ->where('asi.id_usuario', $idUsuario)
            ->where('asi.asistencia', 'asiste')
            ->where('asi.tipo', 'miembro_consejo')
            ->where('NOT EXISTS (SELECT 1 FROM tbl_acta_votacion_votos vv WHERE vv.id_votacion = v.id_votacion AND vv.id_asistente = asi.id_asistente)', null, false)
            ->orderBy('v.id_votacion', 'DESC')
            ->get()->getResultArray();
    }

    /** Actas pendientes de la firma del usuario, con token vigente. */
    public function firmas(int $idUsuario): array
    {
        return $this->db->table('tbl_acta_asistentes asi')
            ->select('a.id_acta, a.numero AS acta_numero, t.token')
            ->join('tbl_actas a', 'a.id_acta = asi.id_acta')
            ->join('tbl_actas_tokens t', "t.id_asistente = asi.id_asistente AND t.tipo = 'firmar_acta' AND t.usado_at IS NULL", 'left')
            ->where('asi.id_usuario', $idUsuario)
            ->where('asi.requiere_firma', 1)
            ->where('asi.firma_estado', 'pendiente')
            ->where('a.estado', 'pendiente_firma')
            ->orderBy('a.id_acta', 'DESC')
            ->get()->getResultArray();
    }

    /** Lista unificada de pendientes con texto y URL para la campana. */
    public function items(int $idUsuario): array
    {
        $items = [];

        foreach ($this->votaciones($idUsuario) as $v) {
            $items[] = [
                'icono'   => '🗳️',
                'texto'   => 'Votación abierta: ' . $v['titulo'],
                'detalle' => 'Acta ' . ($v['acta_numero'] ?? $v['id_acta']),
                'url'     => base_url('actas/' . $v['id_acta'] . '/votaciones'),
            ];
        }

        foreach ($this->firmas($idUsuario) as $f) {
            if (empty($f['token'])) {
                continue;
            }
            $items[] = [
                'icono'   => '✍️',
                'texto'   => 'Firma pendiente: Acta ' . ($f['acta_numero'] ?? $f['id_acta']),
                'detalle' => 'Toca para firmar',
                'url'     => base_url('firmar/' . $f['token']),
            ];
        }

        return $items;
    }
}
