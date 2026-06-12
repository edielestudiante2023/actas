<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaAuditoriaModel extends Model
{
    protected $table         = 'tbl_actas_auditoria';
    protected $primaryKey    = 'id_auditoria';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_acta',
        'id_usuario',
        'accion',
        'detalle',
        'ip',
        'user_agent',
        'created_at',
    ];

    public function registrar(int $idActa, string $accion, ?string $detalle = null): void
    {
        $request = service('request');

        $this->insert([
            'id_acta'    => $idActa,
            'id_usuario' => session('id_usuario'),
            'accion'     => $accion,
            'detalle'    => $detalle,
            'ip'         => $request->getIPAddress(),
            'user_agent' => substr((string) $request->getUserAgent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
