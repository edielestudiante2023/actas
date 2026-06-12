<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActasCore extends Migration
{
    public function up()
    {
        $this->createActas();
        $this->createAsistentes();
        $this->createCompromisos();
        $this->createVotaciones();
        $this->createAnexos();
        $this->createPlantillasOrden();
        $this->createAuditoria();
    }

    public function down()
    {
        $this->forge->dropTable('tbl_actas_auditoria', true);
        $this->forge->dropTable('tbl_actas_plantillas_orden', true);
        $this->forge->dropTable('tbl_acta_anexos', true);
        $this->forge->dropTable('tbl_acta_votaciones', true);
        $this->forge->dropTable('tbl_acta_compromisos', true);
        $this->forge->dropTable('tbl_acta_asistentes', true);
        $this->forge->dropTable('tbl_actas', true);
    }

    private function createActas(): void
    {
        $this->forge->addField([
            'id_acta'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_cliente'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'numero'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'consecutivo'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'titulo'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'fecha'        => ['type' => 'DATE'],
            'hora_inicio'  => ['type' => 'TIME', 'null' => true],
            'hora_fin'     => ['type' => 'TIME', 'null' => true],
            'lugar'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'modalidad'    => ['type' => 'ENUM', 'constraint' => ['presencial', 'virtual', 'mixta'], 'default' => 'presencial'],
            'estado'       => ['type' => 'ENUM', 'constraint' => ['borrador', 'en_edicion', 'pendiente_firma', 'firmada', 'anulada'], 'default' => 'borrador'],
            'objeto'       => ['type' => 'TEXT', 'null' => true],
            'orden_dia'    => ['type' => 'LONGTEXT', 'null' => true],
            'desarrollo'   => ['type' => 'LONGTEXT', 'null' => true],
            'observaciones' => ['type' => 'TEXT', 'null' => true],
            'codigo_verificacion' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'creada_por'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'cerrada_por'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'cerrada_at'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_acta', true);
        $this->forge->addKey(['id_cliente', 'estado']);
        $this->forge->addKey(['id_cliente', 'fecha']);
        $this->forge->addUniqueKey(['id_cliente', 'numero'], 'uk_acta_cliente_numero');
        $this->forge->addUniqueKey('codigo_verificacion', 'uk_acta_codigo_verificacion');
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('creada_por', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('cerrada_por', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_actas', true, $this->tableAttributes());
    }

    private function createAsistentes(): void
    {
        $this->forge->addField([
            'id_asistente' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_usuario'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nombre'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'telefono'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'cargo'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tipo'         => ['type' => 'ENUM', 'constraint' => ['miembro_consejo', 'administracion', 'invitado', 'revisor_fiscal', 'contador', 'abogado', 'otro'], 'default' => 'miembro_consejo'],
            'asistencia'   => ['type' => 'ENUM', 'constraint' => ['asiste', 'no_asiste', 'excusa'], 'default' => 'asiste'],
            'requiere_firma' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'firma_estado' => ['type' => 'ENUM', 'constraint' => ['no_requerida', 'pendiente', 'firmada', 'rechazada', 'ausente'], 'default' => 'pendiente'],
            'firma_imagen' => ['type' => 'LONGTEXT', 'null' => true],
            'firma_ip'     => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'firma_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_asistente', true);
        $this->forge->addKey(['id_acta', 'asistencia']);
        $this->forge->addKey(['id_acta', 'firma_estado']);
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_acta_asistentes', true, $this->tableAttributes());
    }

    private function createCompromisos(): void
    {
        $this->forge->addField([
            'id_compromiso' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'descripcion'   => ['type' => 'TEXT'],
            'id_responsable' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'responsable_nombre' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'fecha_vencimiento' => ['type' => 'DATE', 'null' => true],
            'estado'        => ['type' => 'ENUM', 'constraint' => ['pendiente', 'en_progreso', 'cumplido', 'vencido', 'cancelado'], 'default' => 'pendiente'],
            'avance'        => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_compromiso', true);
        $this->forge->addKey(['id_acta', 'estado']);
        $this->forge->addKey('fecha_vencimiento');
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_responsable', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_acta_compromisos', true, $this->tableAttributes());
    }

    private function createVotaciones(): void
    {
        $this->forge->addField([
            'id_votacion' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'titulo'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'descripcion' => ['type' => 'TEXT', 'null' => true],
            'votos_favor' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'votos_contra' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'abstenciones' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'resultado'   => ['type' => 'ENUM', 'constraint' => ['pendiente', 'aprobada', 'rechazada'], 'default' => 'pendiente'],
            'detalle_votos' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_votacion', true);
        $this->forge->addKey(['id_acta', 'resultado']);
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_acta_votaciones', true, $this->tableAttributes());
    }

    private function createAnexos(): void
    {
        $this->forge->addField([
            'id_anexo'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'archivo'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime'        => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'tamano'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'subido_por'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_anexo', true);
        $this->forge->addKey('id_acta');
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('subido_por', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_acta_anexos', true, $this->tableAttributes());
    }

    private function createPlantillasOrden(): void
    {
        $this->forge->addField([
            'id_plantilla' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_cliente'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nombre'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'descripcion'  => ['type' => 'TEXT', 'null' => true],
            'items'        => ['type' => 'LONGTEXT', 'null' => true],
            'activo'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_plantilla', true);
        $this->forge->addKey(['id_cliente', 'activo']);
        $this->forge->addForeignKey('id_cliente', 'tbl_clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_actas_plantillas_orden', true, $this->tableAttributes());
    }

    private function createAuditoria(): void
    {
        $this->forge->addField([
            'id_auditoria' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_acta'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_usuario'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'accion'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'detalle'      => ['type' => 'TEXT', 'null' => true],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_auditoria', true);
        $this->forge->addKey(['id_acta', 'accion']);
        $this->forge->addForeignKey('id_acta', 'tbl_actas', 'id_acta', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_usuario', 'tbl_usuarios', 'id_usuario', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_actas_auditoria', true, $this->tableAttributes());
    }

    private function tableAttributes(): array
    {
        return ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci'];
    }
}
