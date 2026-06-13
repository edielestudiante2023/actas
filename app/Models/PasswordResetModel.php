<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table         = 'tbl_password_resets';
    protected $primaryKey    = 'id_reset';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_usuario',
        'token_hash',
        'expires_at',
        'used_at',
        'ip',
        'created_at',
    ];

    public function crear(int $idUsuario, string $ip, int $minutos = 60): string
    {
        $this->where('id_usuario', $idUsuario)
            ->where('used_at', null)
            ->set(['used_at' => date('Y-m-d H:i:s')])
            ->update();

        $token = bin2hex(random_bytes(32));
        $this->insert([
            'id_usuario' => $idUsuario,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $minutos * 60),
            'used_at'    => null,
            'ip'         => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function findValid(string $token): ?array
    {
        $row = $this->db->table($this->table)
            ->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        if (strtotime((string) $row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    public function marcarUsado(int $idReset): void
    {
        $this->update($idReset, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
