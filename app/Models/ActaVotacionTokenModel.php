<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVotacionTokenModel extends Model
{
    protected $table         = 'tbl_acta_votacion_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['token', 'id_votacion', 'id_asistente', 'id_cliente', 'expires_at', 'usado_at', 'ip_uso', 'created_at'];

    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    /** Token utilizable = existe y no expirado (la validez final la da el estado de la votación). */
    public function findUsable(string $token): ?array
    {
        $row = $this->findByToken($token);
        if ($row === null) {
            return null;
        }
        if (! empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    public function tokenDeAsistente(int $idVotacion, int $idAsistente): ?array
    {
        return $this->where('id_votacion', $idVotacion)
            ->where('id_asistente', $idAsistente)
            ->first();
    }

    public function nuevoToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
