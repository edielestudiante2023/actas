<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActaSolicitudes extends Migration
{
    public function up()
    {
        $this->createSolicitudesAusente();
        $this->createSolicitudesReapertura();
    }

    public function down()
    {
        $this->forge->dropTable('tbl_acta_solicitudes_reapertura', true);
        $this->forge->dropTable('tbl_acta_solicitudes_ausente', true);
    }

    private function createSolicitudesAusente(): void
    {
        $this->forge->addField($this->baseFields(false));
        $this->forge->addKey('id_solicitud', true);
        $this->forge->addKey(['id_acta', 'estado']);
        $this->forge->addKey(['id_cliente', 'estado']);
        $this->forge->addUniqueKey('token_hash', 'uk_solicitud_ausente_token');
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asistente', 'tbl_acta_asistentes', 'id_asistente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resuelta_por', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_acta_solicitudes_ausente', true, $this->tableAttributes());
    }

    private function createSolicitudesReapertura(): void
    {
        $this->forge->addField($this->baseFields(true));
        $this->forge->addKey('id_solicitud', true);
        $this->forge->addKey(['id_acta', 'estado']);
        $this->forge->addKey(['id_cliente', 'estado']);
        $this->forge->addUniqueKey('token_hash', 'uk_solicitud_reapertura_token');
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_asistente', 'tbl_acta_asistentes', 'id_asistente', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resuelta_por', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_acta_solicitudes_reapertura', true, $this->tableAttributes());
    }

    private function baseFields(bool $asistenteNullable): array
    {
        return [
            'id_solicitud'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_asistente'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => $asistenteNullable],
            'id_cliente'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'solicitante_nombre' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'solicitante_email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'motivo'            => ['type' => 'TEXT'],
            'estado'            => ['type' => 'ENUM', 'constraint' => ['pendiente', 'aprobada', 'rechazada'], 'default' => 'pendiente'],
            'token_hash'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at'        => ['type' => 'DATETIME'],
            'resuelta_por'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resuelta_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ];
    }

    private function tableAttributes(): array
    {
        return ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci'];
    }
}
