<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaAnexoModel extends Model
{
    protected $table         = 'tbl_acta_anexos';
    protected $primaryKey    = 'id_anexo';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // la tabla solo tiene created_at (se setea manual)
    protected $allowedFields = [
        'id_acta',
        'nombre',
        'archivo',
        'mime',
        'tamano',
        'subido_por',
        'created_at',
    ];

    public function anexosActa(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
            ->orderBy('id_anexo', 'ASC')
            ->findAll();
    }

    public function findForActa(int $idAnexo, int $idActa): ?array
    {
        return $this->where('id_anexo', $idAnexo)
            ->where('id_acta', $idActa)
            ->first();
    }
}
