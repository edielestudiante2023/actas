<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVotacionTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'token'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'id_votacion'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_asistente' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_cliente'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expires_at'   => ['type' => 'DATETIME', 'null' => true],
            'usado_at'     => ['type' => 'DATETIME', 'null' => true],
            'ip_uso'       => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('id_votacion');
        $this->forge->addUniqueKey(['id_votacion', 'id_asistente'], 'uk_voto_token');
        $this->forge->addForeignKey('id_votacion', 'tbl_acta_votaciones', 'id_votacion', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asistente', 'tbl_acta_asistentes', 'id_asistente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_acta_votacion_tokens', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_acta_votacion_tokens', true);
    }
}
