<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInmuebleConsejo extends Migration
{
    public function up()
    {
        // El inmueble (unidad de copropiedad) que representa cada miembro del consejo.
        $this->forge->addColumn('tbl_cliente_consejo', [
            'inmueble'    => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'cargo'],
            'coeficiente' => ['type' => 'DECIMAL', 'constraint' => '8,5', 'null' => true, 'after' => 'inmueble'],
        ]);

        // Snapshot del inmueble en el asistente del acta.
        $this->forge->addColumn('tbl_acta_asistentes', [
            'inmueble' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'cargo'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_cliente_consejo', ['inmueble', 'coeficiente']);
        $this->forge->dropColumn('tbl_acta_asistentes', ['inmueble']);
    }
}
