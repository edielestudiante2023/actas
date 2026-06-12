<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameConjuntosToClientes extends Migration
{
    public function up()
    {
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_usuario_foreign');
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_rol_foreign');
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_conjunto_foreign');
        $this->dropIndexIfExists('tbl_usuario_rol', 'uk_usuario_rol_conjunto');
        $this->dropIndexIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_conjunto_foreign');

        $this->forge->renameTable('tbl_conjuntos', 'tbl_clientes');
        $this->forge->modifyColumn('tbl_clientes', [
            'id_conjunto' => [
                'name'           => 'id_cliente',
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
        ]);

        $this->forge->modifyColumn('tbl_usuario_rol', [
            'id_conjunto' => [
                'name'       => 'id_cliente',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addUniqueKey(['id_usuario', 'id_rol', 'id_cliente'], 'uk_usuario_rol_cliente');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_rol', 'tbl_roles', 'id_rol', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('tbl_usuario_rol');
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_usuario_foreign');
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_rol_foreign');
        $this->dropForeignKeyIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_cliente_foreign');
        $this->dropIndexIfExists('tbl_usuario_rol', 'uk_usuario_rol_cliente');
        $this->dropIndexIfExists('tbl_usuario_rol', 'tbl_usuario_rol_id_cliente_foreign');

        $this->forge->modifyColumn('tbl_usuario_rol', [
            'id_cliente' => [
                'name'       => 'id_conjunto',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->modifyColumn('tbl_clientes', [
            'id_cliente' => [
                'name'           => 'id_conjunto',
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
        ]);
        $this->forge->renameTable('tbl_clientes', 'tbl_conjuntos');

        $this->forge->addUniqueKey(['id_usuario', 'id_rol', 'id_conjunto'], 'uk_usuario_rol_conjunto');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_rol', 'tbl_roles', 'id_rol', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_conjunto', 'tbl_conjuntos', 'id_conjunto', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('tbl_usuario_rol');
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        foreach ($this->db->getForeignKeyData($table) as $key) {
            if (($key->constraint_name ?? null) === $foreignKey) {
                $this->forge->dropForeignKey($table, $foreignKey);
                return;
            }
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        foreach ($this->db->getIndexData($table) as $key) {
            if (($key->name ?? null) === $index) {
                $this->forge->dropKey($table, $index);
                return;
            }
        }
    }
}
