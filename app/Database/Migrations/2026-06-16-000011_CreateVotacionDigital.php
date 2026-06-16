<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVotacionDigital extends Migration
{
    public function up()
    {
        // Estado/modo en votaciones. Las existentes quedan manual/cerrada (compatibilidad).
        $this->forge->addColumn('tbl_acta_votaciones', [
            'estado' => ['type' => 'ENUM', 'constraint' => ['abierta', 'cerrada'], 'default' => 'cerrada', 'after' => 'resultado'],
            'modo'   => ['type' => 'ENUM', 'constraint' => ['manual', 'digital'], 'default' => 'manual', 'after' => 'estado'],
        ]);

        // Voto nominal por miembro (un voto por asistente por votación).
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_votacion'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_asistente' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'voto'         => ['type' => 'ENUM', 'constraint' => ['favor', 'contra', 'abstencion']],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'voted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_votacion', 'id_asistente'], 'uk_votacion_asistente');
        $this->forge->addForeignKey('id_votacion', 'tbl_acta_votaciones', 'id_votacion', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asistente', 'tbl_acta_asistentes', 'id_asistente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_acta_votacion_votos', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_acta_votacion_votos', true);
        $this->forge->dropColumn('tbl_acta_votaciones', ['estado', 'modo']);
    }
}
