<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteConsejoModel extends Model
{
    protected $table         = 'tbl_cliente_consejo';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_cliente',
        'id_usuario',
        'cargo',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function miembrosCliente(int $idCliente): array
    {
        return $this->select('tbl_cliente_consejo.*, u.nombre_completo, u.email, u.telefono')
            ->join('tbl_usuarios u', 'u.id_usuario = tbl_cliente_consejo.id_usuario')
            ->where('tbl_cliente_consejo.id_cliente', $idCliente)
            ->orderBy('tbl_cliente_consejo.estado', 'ASC')
            ->orderBy("FIELD(tbl_cliente_consejo.cargo, 'presidente_consejo', 'consejero')", '', false)
            ->orderBy('u.nombre_completo', 'ASC')
            ->findAll();
    }

    public function existeActivo(int $idCliente, int $idUsuario, string $cargo): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('id_usuario', $idUsuario)
            ->where('cargo', $cargo)
            ->where('estado', 'activo')
            ->first() !== null;
    }

    public function cerrarPresidenteActivo(int $idCliente): void
    {
        $this->where('id_cliente', $idCliente)
            ->where('cargo', 'presidente_consejo')
            ->where('estado', 'activo')
            ->set([
                'estado' => 'inactivo',
                'fecha_fin' => date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }
}
