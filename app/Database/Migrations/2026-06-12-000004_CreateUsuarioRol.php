<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarioRol extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_usuario'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_rol'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_conjunto' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'estado'      => ['type' => 'ENUM', 'constraint' => ['activo', 'inactivo'], 'default' => 'activo'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Un usuario no puede tener el mismo rol repetido en el mismo conjunto.
        $this->forge->addUniqueKey(['id_usuario', 'id_rol', 'id_conjunto'], 'uk_usuario_rol_conjunto');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_rol', 'tbl_roles', 'id_rol', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_conjunto', 'tbl_conjuntos', 'id_conjunto', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_usuario_rol', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_usuario_rol', true);
    }
}
