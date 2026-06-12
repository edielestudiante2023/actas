<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioRolModel extends Model
{
    protected $table         = 'tbl_usuario_rol';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_usuario',
        'id_rol',
        'id_cliente',
        'estado',
        'created_at',
    ];

    public function getAsignaciones(int $idUsuario): array
    {
        return $this->select('tbl_usuario_rol.*, r.codigo, r.nombre AS rol_nombre, c.nombre AS cliente_nombre')
            ->join('tbl_roles r', 'r.id_rol = tbl_usuario_rol.id_rol')
            ->join('tbl_clientes c', 'c.id_cliente = tbl_usuario_rol.id_cliente', 'left')
            ->where('tbl_usuario_rol.id_usuario', $idUsuario)
            ->where('tbl_usuario_rol.estado', 'activo')
            ->orderBy('r.nivel', 'DESC')
            ->orderBy('c.nombre', 'ASC')
            ->findAll();
    }

    public function getAsignacionesPorUsuarios(array $idsUsuarios): array
    {
        $idsUsuarios = array_values(array_unique(array_map('intval', $idsUsuarios)));
        if ($idsUsuarios === []) {
            return [];
        }

        return $this->select('tbl_usuario_rol.*, r.codigo, r.nombre AS rol_nombre, c.nombre AS cliente_nombre')
            ->join('tbl_roles r', 'r.id_rol = tbl_usuario_rol.id_rol')
            ->join('tbl_clientes c', 'c.id_cliente = tbl_usuario_rol.id_cliente', 'left')
            ->whereIn('tbl_usuario_rol.id_usuario', $idsUsuarios)
            ->where('tbl_usuario_rol.estado', 'activo')
            ->orderBy('r.nivel', 'DESC')
            ->orderBy('c.nombre', 'ASC')
            ->findAll();
    }

    public function syncAsignaciones(int $idUsuario, array $asignaciones): void
    {
        $now = date('Y-m-d H:i:s');

        $this->where('id_usuario', $idUsuario)->set(['estado' => 'inactivo'])->update();

        foreach ($asignaciones as $asignacion) {
            $idRol = (int) ($asignacion['id_rol'] ?? 0);
            $idCliente = $asignacion['id_cliente'] ?? null;
            $idCliente = $idCliente === null ? null : (int) $idCliente;

            if ($idRol <= 0) {
                continue;
            }

            $existente = $this->where('id_usuario', $idUsuario)
                ->where('id_rol', $idRol)
                ->where('id_cliente', $idCliente)
                ->first();

            if ($existente !== null) {
                $this->update($existente['id'], ['estado' => 'activo']);
                continue;
            }

            $this->insert([
                'id_usuario'  => $idUsuario,
                'id_rol'      => $idRol,
                'id_cliente'  => $idCliente,
                'estado'      => 'activo',
                'created_at'  => $now,
            ]);
        }
    }
}
