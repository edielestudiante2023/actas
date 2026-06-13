<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActasTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_token'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'token'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'tipo'         => ['type' => 'ENUM', 'constraint' => ['firmar_acta', 'ver_acta'], 'default' => 'firmar_acta'],
            'id_acta'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_asistente' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_cliente'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expires_at'   => ['type' => 'DATETIME', 'null' => true],
            'usado_at'     => ['type' => 'DATETIME', 'null' => true],
            'ip_uso'       => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_token', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('id_acta');
        $this->forge->addKey('id_asistente');
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asistente', 'tbl_acta_asistentes', 'id_asistente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_actas_tokens', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_actas_tokens', true);
    }
}
