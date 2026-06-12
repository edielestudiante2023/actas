<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_usuario'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre_completo' => ['type' => 'VARCHAR', 'constraint' => 200],
            'tipo_documento'  => ['type' => 'ENUM', 'constraint' => ['CC', 'CE', 'PA', 'NIT'], 'default' => 'CC'],
            'numero_documento' => ['type' => 'VARCHAR', 'constraint' => 20],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'telefono'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'estado'          => ['type' => 'ENUM', 'constraint' => ['activo', 'inactivo', 'bloqueado'], 'default' => 'activo'],
            'ultimo_acceso'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_usuario', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('numero_documento');
        $this->forge->addKey('estado');
        $this->forge->createTable('tbl_usuarios', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_usuarios', true);
    }
}
