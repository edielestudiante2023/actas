<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaSolicitudAusenteModel extends Model
{
    protected $table         = 'tbl_acta_solicitudes_ausente';
    protected $primaryKey    = 'id_solicitud';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_acta',
        'id_asistente',
        'id_cliente',
        'solicitante_nombre',
        'solicitante_email',
        'motivo',
        'estado',
        'token_hash',
        'expires_at',
        'resuelta_por',
        'resuelta_at',
        'created_at',
    ];

    public function solicitudesActa(int $idActa): array
    {
        return $this->select('tbl_acta_solicitudes_ausente.*, a.nombre AS asistente_nombre, a.cargo AS asistente_cargo')
            ->join('tbl_acta_asistentes a', 'a.id_asistente = tbl_acta_solicitudes_ausente.id_asistente', 'left')
            ->where('tbl_acta_solicitudes_ausente.id_acta', $idActa)
            ->orderBy("FIELD(tbl_acta_solicitudes_ausente.estado, 'pendiente', 'aprobada', 'rechazada')", '', false)
            ->orderBy('tbl_acta_solicitudes_ausente.created_at', 'DESC')
            ->findAll();
    }

    public function pendienteParaAsistente(int $idActa, int $idAsistente): ?array
    {
        return $this->where('id_acta', $idActa)
            ->where('id_asistente', $idAsistente)
            ->where('estado', 'pendiente')
            ->first();
    }

    public function nuevoTokenHash(): string
    {
        return hash('sha256', bin2hex(random_bytes(32)));
    }
}
