<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConjuntos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_conjunto' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'nit'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'direccion'   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'ciudad'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'telefono'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'logo'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'estado'      => ['type' => 'ENUM', 'constraint' => ['activo', 'inactivo'], 'default' => 'activo'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_conjunto', true);
        $this->forge->addKey('estado');
        $this->forge->createTable('tbl_conjuntos', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_conjuntos', true);
    }
}
