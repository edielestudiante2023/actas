<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVotacionModel extends Model
{
    protected $table         = 'tbl_acta_votaciones';
    protected $primaryKey    = 'id_votacion';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_acta',
        'titulo',
        'descripcion',
        'votos_favor',
        'votos_contra',
        'abstenciones',
        'resultado',
        'detalle_votos',
    ];

    public function votacionesActa(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
            ->orderBy('id_votacion', 'ASC')
            ->findAll();
    }

    public function findForActa(int $idVotacion, int $idActa): ?array
    {
        return $this->where('id_votacion', $idVotacion)
            ->where('id_acta', $idActa)
            ->first();
    }
}
