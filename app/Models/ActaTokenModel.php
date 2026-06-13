<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaTokenModel extends Model
{
    protected $table         = 'tbl_actas_tokens';
    protected $primaryKey    = 'id_token';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'token',
        'tipo',
        'id_acta',
        'id_asistente',
        'id_cliente',
        'expires_at',
        'usado_at',
        'ip_uso',
        'created_at',
    ];

    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    /**
     * Token válido = existe, sin usar y no expirado.
     */
    public function findValid(string $token): ?array
    {
        $row = $this->findByToken($token);
        if ($row === null) {
            return null;
        }
        if (! empty($row['usado_at'])) {
            return null;
        }
        if (! empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    /**
     * Tokens de firma vigentes de un acta, indexados por id_asistente.
     */
    public function firmaTokensPorAsistente(int $idActa): array
    {
        $rows = $this->where('id_acta', $idActa)
            ->where('tipo', 'firmar_acta')
            ->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_asistente']] = $row;
        }

        return $map;
    }

    public function eliminarFirmaTokens(int $idActa): void
    {
        $this->where('id_acta', $idActa)
            ->where('tipo', 'firmar_acta')
            ->delete();
    }

    public function nuevoToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
