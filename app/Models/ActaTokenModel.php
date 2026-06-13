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

    public function findFirmaToken(int $idActa, int $idAsistente): ?array
    {
        return $this->where('id_acta', $idActa)
            ->where('id_asistente', $idAsistente)
            ->where('tipo', 'firmar_acta')
            ->first();
    }

    public function cancelarFirmaToken(int $idActa, int $idAsistente, string $ip): bool
    {
        $token = $this->findFirmaToken($idActa, $idAsistente);
        if ($token === null || ! empty($token['usado_at'])) {
            return false;
        }

        return $this->update($token['id_token'], [
            'usado_at' => date('Y-m-d H:i:s'),
            'ip_uso'   => $ip,
        ]);
    }

    public function regenerarFirmaToken(int $idActa, int $idAsistente, int $idCliente, int $diasExpira): array
    {
        $token = $this->findFirmaToken($idActa, $idAsistente);
        $data = [
            'token'      => $this->nuevoToken(),
            'expires_at' => date('Y-m-d H:i:s', time() + $diasExpira * 86400),
            'usado_at'   => null,
            'ip_uso'     => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($token === null) {
            $data['tipo'] = 'firmar_acta';
            $data['id_acta'] = $idActa;
            $data['id_asistente'] = $idAsistente;
            $data['id_cliente'] = $idCliente;
            $idToken = $this->insert($data, true);

            return $this->find((int) $idToken);
        }

        $this->update($token['id_token'], $data);

        return $this->find((int) $token['id_token']);
    }

    public function nuevoToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
