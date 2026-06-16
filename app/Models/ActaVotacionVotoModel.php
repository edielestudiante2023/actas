<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVotacionVotoModel extends Model
{
    protected $table         = 'tbl_acta_votacion_votos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['id_votacion', 'id_asistente', 'voto', 'ip', 'voted_at'];

    public function conteo(int $idVotacion): array
    {
        $rows = $this->select('voto, COUNT(*) AS n')
            ->where('id_votacion', $idVotacion)
            ->groupBy('voto')
            ->findAll();

        $c = ['favor' => 0, 'contra' => 0, 'abstencion' => 0];
        foreach ($rows as $r) {
            $c[$r['voto']] = (int) $r['n'];
        }
        $c['total'] = $c['favor'] + $c['contra'] + $c['abstencion'];

        return $c;
    }

    public function miVoto(int $idVotacion, int $idAsistente): ?string
    {
        $r = $this->where('id_votacion', $idVotacion)
            ->where('id_asistente', $idAsistente)
            ->first();

        return $r['voto'] ?? null;
    }

    /**
     * Registra (o cambia, mientras la votación esté abierta) el voto de un asistente.
     */
    public function registrar(int $idVotacion, int $idAsistente, string $voto, string $ip): void
    {
        $existe = $this->where('id_votacion', $idVotacion)
            ->where('id_asistente', $idAsistente)
            ->first();

        $data = ['voto' => $voto, 'ip' => $ip, 'voted_at' => date('Y-m-d H:i:s')];
        if ($existe) {
            $this->update($existe['id'], $data);
        } else {
            $this->insert($data + ['id_votacion' => $idVotacion, 'id_asistente' => $idAsistente]);
        }
    }
}
