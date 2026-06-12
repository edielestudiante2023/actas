<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'tbl_usuarios';
    protected $primaryKey    = 'id_usuario';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nombre_completo', 'tipo_documento', 'numero_documento', 'email',
        'telefono', 'password', 'estado', 'ultimo_acceso', 'created_at', 'updated_at',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Roles del usuario con su cliente (id_cliente NULL = plataforma/superadmin).
     */
    public function getRoles(int $idUsuario): array
    {
        return $this->db->table('tbl_usuario_rol ur')
            ->select('r.id_rol, r.codigo, r.nombre, r.nivel, ur.id_cliente')
            ->join('tbl_roles r', 'r.id_rol = ur.id_rol')
            ->where('ur.id_usuario', $idUsuario)
            ->where('ur.estado', 'activo')
            ->orderBy('r.nivel', 'DESC')
            ->get()->getResultArray();
    }
}
