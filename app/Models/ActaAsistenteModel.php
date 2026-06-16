<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaAsistenteModel extends Model
{
    protected $table         = 'tbl_acta_asistentes';
    protected $primaryKey    = 'id_asistente';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_acta',
        'id_usuario',
        'nombre',
        'email',
        'telefono',
        'cargo',
        'inmueble',
        'tipo',
        'asistencia',
        'requiere_firma',
        'firma_estado',
        'firma_imagen',
        'firma_ip',
        'firma_at',
    ];

    public function asistentesActa(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
            ->orderBy("FIELD(cargo, 'Presidente del Consejo', 'Consejero')", '', false)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    public function importarConsejo(int $idActa, int $idCliente): int
    {
        $miembros = $this->db->table('tbl_cliente_consejo cc')
            ->select('cc.cargo, cc.inmueble, u.id_usuario, u.nombre_completo, u.email, u.telefono')
            ->join('tbl_usuarios u', 'u.id_usuario = cc.id_usuario')
            ->where('cc.id_cliente', $idCliente)
            ->where('cc.estado', 'activo')
            ->where('u.estado', 'activo')
            ->orderBy("FIELD(cc.cargo, 'presidente_consejo', 'consejero')", '', false)
            ->orderBy('u.nombre_completo', 'ASC')
            ->get()
            ->getResultArray();

        $insertados = 0;
        foreach ($miembros as $miembro) {
            $existe = $this->where('id_acta', $idActa)
                ->where('id_usuario', $miembro['id_usuario'])
                ->first();

            if ($existe !== null) {
                continue;
            }

            $this->insert([
                'id_acta'        => $idActa,
                'id_usuario'     => $miembro['id_usuario'],
                'nombre'         => $miembro['nombre_completo'],
                'email'          => $miembro['email'],
                'telefono'       => $miembro['telefono'],
                'cargo'          => $miembro['cargo'] === 'presidente_consejo' ? 'Presidente del Consejo' : 'Consejero',
                'inmueble'       => $miembro['inmueble'] ?? null,
                'tipo'           => 'miembro_consejo',
                'asistencia'     => 'asiste',
                'requiere_firma' => 1,
                'firma_estado'   => 'pendiente',
            ]);
            $insertados++;
        }

        return $insertados;
    }

    public function resumenQuorum(int $idActa): array
    {
        $total = $this->where('id_acta', $idActa)
            ->where('tipo', 'miembro_consejo')
            ->countAllResults();

        $presentes = $this->where('id_acta', $idActa)
            ->where('tipo', 'miembro_consejo')
            ->where('asistencia', 'asiste')
            ->countAllResults();

        $requerido = $total > 0 ? (int) floor($total / 2) + 1 : 0;

        return [
            'total'     => $total,
            'presentes' => $presentes,
            'requerido' => $requerido,
            'cumple'    => $total > 0 && $presentes >= $requerido,
        ];
    }

    /**
     * Asistente del acta que corresponde al usuario en sesión (para votar en-app).
     */
    public function asistenteDeUsuario(int $idActa, int $idUsuario): ?array
    {
        return $this->where('id_acta', $idActa)
            ->where('id_usuario', $idUsuario)
            ->first();
    }
}
