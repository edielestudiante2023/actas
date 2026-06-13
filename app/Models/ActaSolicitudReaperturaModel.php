<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaSolicitudReaperturaModel extends Model
{
    protected $table         = 'tbl_acta_solicitudes_reapertura';
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
        return $this->where('id_acta', $idActa)
            ->orderBy("FIELD(estado, 'pendiente', 'aprobada', 'rechazada')", '', false)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function nuevoTokenHash(): string
    {
        return hash('sha256', bin2hex(random_bytes(32)));
    }
}
