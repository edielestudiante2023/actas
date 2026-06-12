<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table         = 'tbl_clientes';
    protected $primaryKey    = 'id_cliente';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'nombre',
        'nit',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'logo',
        'estado',
    ];
}
