<?php

namespace App\Models;

use CodeIgniter\Model;

class RolModel extends Model
{
    protected $table         = 'tbl_roles';
    protected $primaryKey    = 'id_rol';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'codigo',
        'nombre',
        'descripcion',
        'nivel',
        'activo',
        'created_at',
    ];

    public function activos(): array
    {
        return $this->where('activo', 1)
            ->orderBy('nivel', 'DESC')
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }
}
