<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaCompromisoModel extends Model
{
    protected $table         = 'tbl_acta_compromisos';
    protected $primaryKey    = 'id_compromiso';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_acta',
        'descripcion',
        'id_responsable',
        'responsable_nombre',
        'fecha_vencimiento',
        'estado',
        'avance',
    ];

    public function compromisosActa(int $idActa): array
    {
        return $this->select('tbl_acta_compromisos.*, u.nombre_completo AS usuario_nombre, u.email AS usuario_email')
            ->join('tbl_usuarios u', 'u.id_usuario = tbl_acta_compromisos.id_responsable', 'left')
            ->where('tbl_acta_compromisos.id_acta', $idActa)
            ->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')
            ->orderBy('tbl_acta_compromisos.id_compromiso', 'ASC')
            ->findAll();
    }

    public function responsablesActa(int $idActa): array
    {
        return $this->db->table('tbl_acta_asistentes a')
            ->select('a.id_usuario, a.nombre, a.email, a.cargo')
            ->where('a.id_acta', $idActa)
            ->where('a.asistencia', 'asiste')
            ->where('a.id_usuario IS NOT NULL', null, false)
            ->groupBy('a.id_usuario, a.nombre, a.email, a.cargo')
            ->orderBy("FIELD(a.cargo, 'Presidente del Consejo', 'Consejero')", '', false)
            ->orderBy('a.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findForActa(int $idCompromiso, int $idActa): ?array
    {
        return $this->where('id_compromiso', $idCompromiso)
            ->where('id_acta', $idActa)
            ->first();
    }
}
