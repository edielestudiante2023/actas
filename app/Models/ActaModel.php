<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaModel extends Model
{
    protected $table         = 'tbl_actas';
    protected $primaryKey    = 'id_acta';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_cliente',
        'numero',
        'consecutivo',
        'titulo',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'lugar',
        'modalidad',
        'estado',
        'objeto',
        'orden_dia',
        'desarrollo',
        'observaciones',
        'codigo_verificacion',
        'creada_por',
        'cerrada_por',
        'cerrada_at',
    ];

    public function nextConsecutivo(int $idCliente): int
    {
        $max = $this->selectMax('consecutivo')
            ->where('id_cliente', $idCliente)
            ->first();

        return ((int) ($max['consecutivo'] ?? 0)) + 1;
    }

    public function buildNumero(int $idCliente, int $consecutivo, string $fecha): string
    {
        $year = date('Y', strtotime($fecha));

        return $year . '-' . str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT);
    }

    public function findForCliente(int $idActa, int $idCliente): ?array
    {
        return $this->where('id_acta', $idActa)
            ->where('id_cliente', $idCliente)
            ->first();
    }
}
