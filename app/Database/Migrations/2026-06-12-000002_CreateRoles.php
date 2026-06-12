<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_rol'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'codigo'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'descripcion' => ['type' => 'TEXT', 'null' => true],
            'nivel'       => ['type' => 'TINYINT', 'constraint' => 4, 'unsigned' => true, 'default' => 0],
            'activo'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_rol', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->createTable('tbl_roles', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_roles', true);
    }
}
