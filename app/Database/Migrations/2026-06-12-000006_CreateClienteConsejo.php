<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClienteConsejo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_cliente'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_usuario'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'cargo'       => ['type' => 'ENUM', 'constraint' => ['presidente_consejo', 'consejero']],
            'estado'      => ['type' => 'ENUM', 'constraint' => ['activo', 'inactivo'], 'default' => 'activo'],
            'fecha_inicio' => ['type' => 'DATE', 'null' => true],
            'fecha_fin'   => ['type' => 'DATE', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_cliente', 'estado']);
        $this->forge->addKey(['id_cliente', 'cargo', 'estado']);
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_cliente_consejo', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_cliente_consejo', true);
    }
}
