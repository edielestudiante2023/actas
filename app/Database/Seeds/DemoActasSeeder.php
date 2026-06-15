<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoActasSeeder extends Seeder
{
    private const CLIENTE_NIT = '900999001-1';
    private const DEMO_PASSWORD = 'Demo12345';

    private array $roles = [];
    private array $usuarios = [];
    private array $firmas = [];

    public function run()
    {
        $this->db->transStart();

        $this->roles = $this->rolesPorCodigo();
        $this->firmas = [
            'edison'  => $this->assetDataUri(FCPATH . 'assets/demo/firma-demo-edison.png'),
            'natalia' => $this->assetDataUri(FCPATH . 'assets/demo/firma-demo-natalia.png'),
        ];

        $idCliente = $this->upsertCliente();
        $this->usuarios = $this->upsertUsuarios($idCliente);
        $this->upsertConsejo($idCliente);
        $this->recrearActas($idCliente);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            echo "ERROR: no fue posible crear datos demo.\n";
            return;
        }

        echo "Demo listo. Cliente: Conjunto Demo PWA Cycloid (id={$idCliente}).\n";
        echo "Usuarios demo: admin.demo@cycloid.test / " . self::DEMO_PASSWORD . "\n";
    }

    private function upsertCliente(): int
    {
        $now = date('Y-m-d H:i:s');
        $logo = $this->storeDemoLogo();
        $table = $this->db->table('tbl_clientes');
        $cliente = $table->where('nit', self::CLIENTE_NIT)->get()->getRowArray();
        $data = [
            'nombre'     => 'Conjunto Demo PWA Cycloid',
            'nit'        => self::CLIENTE_NIT,
            'direccion'  => 'Calle 123 # 45-67',
            'ciudad'     => 'Bogota',
            'telefono'   => '6015550101',
            'email'      => 'administracion.demo@cycloid.test',
            'logo'       => $logo,
            'estado'     => 'activo',
            'updated_at' => $now,
        ];

        if ($cliente === null) {
            $data['created_at'] = $now;
            $table->insert($data);

            return (int) $this->db->insertID();
        }

        $table->where('id_cliente', $cliente['id_cliente'])->update($data);

        return (int) $cliente['id_cliente'];
    }

    private function upsertUsuarios(int $idCliente): array
    {
        $usuarios = [
            'admin' => [
                'nombre' => 'Administracion Demo',
                'doc'    => 'DEMO1001',
                'email'  => 'admin.demo@cycloid.test',
                'tel'    => '3001110001',
                'roles'  => ['administrador'],
            ],
            'edison' => [
                'nombre' => 'Edison Cuervo Demo',
                'doc'    => 'DEMO1002',
                'email'  => 'edison.demo@cycloid.test',
                'tel'    => '3001110002',
                'roles'  => ['presidente_consejo', 'consejero'],
            ],
            'natalia' => [
                'nombre' => 'Natalia Pardo Demo',
                'doc'    => 'DEMO1003',
                'email'  => 'natalia.demo@cycloid.test',
                'tel'    => '3001110003',
                'roles'  => ['consejero'],
            ],
            'carlos' => [
                'nombre' => 'Carlos Mendoza Demo',
                'doc'    => 'DEMO1004',
                'email'  => 'carlos.demo@cycloid.test',
                'tel'    => '3001110004',
                'roles'  => ['consejero'],
            ],
            'laura' => [
                'nombre' => 'Laura Torres Demo',
                'doc'    => 'DEMO1005',
                'email'  => 'laura.demo@cycloid.test',
                'tel'    => '3001110005',
                'roles'  => ['contador'],
            ],
        ];

        $ids = [];
        foreach ($usuarios as $key => $usuario) {
            $ids[$key] = $this->upsertUsuario($usuario);
            foreach ($usuario['roles'] as $codigoRol) {
                $this->upsertUsuarioRol($ids[$key], $this->roles[$codigoRol], $idCliente);
            }
        }

        return $ids;
    }

    private function upsertUsuario(array $usuario): int
    {
        $now = date('Y-m-d H:i:s');
        $table = $this->db->table('tbl_usuarios');
        $row = $table->where('email', $usuario['email'])->get()->getRowArray();
        $data = [
            'nombre_completo'  => $usuario['nombre'],
            'tipo_documento'   => 'CC',
            'numero_documento' => $usuario['doc'],
            'email'            => $usuario['email'],
            'telefono'         => $usuario['tel'],
            'password'         => password_hash(self::DEMO_PASSWORD, PASSWORD_BCRYPT),
            'estado'           => 'activo',
            'updated_at'       => $now,
        ];

        if ($row === null) {
            $data['created_at'] = $now;
            $table->insert($data);

            return (int) $this->db->insertID();
        }

        $table->where('id_usuario', $row['id_usuario'])->update($data);

        return (int) $row['id_usuario'];
    }

    private function upsertUsuarioRol(int $idUsuario, int $idRol, int $idCliente): void
    {
        $table = $this->db->table('tbl_usuario_rol');
        $row = $table
            ->where('id_usuario', $idUsuario)
            ->where('id_rol', $idRol)
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if ($row === null) {
            $table->insert([
                'id_usuario' => $idUsuario,
                'id_rol'     => $idRol,
                'id_cliente' => $idCliente,
                'estado'     => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        $table->where('id', $row['id'])->update(['estado' => 'activo']);
    }

    private function upsertConsejo(int $idCliente): void
    {
        $this->db->table('tbl_cliente_consejo')
            ->where('id_cliente', $idCliente)
            ->set(['estado' => 'inactivo', 'fecha_fin' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s')])
            ->update();

        $miembros = [
            ['id_usuario' => $this->usuarios['edison'], 'cargo' => 'presidente_consejo'],
            ['id_usuario' => $this->usuarios['natalia'], 'cargo' => 'consejero'],
            ['id_usuario' => $this->usuarios['carlos'], 'cargo' => 'consejero'],
        ];

        foreach ($miembros as $miembro) {
            $this->db->table('tbl_cliente_consejo')->insert([
                'id_cliente'   => $idCliente,
                'id_usuario'   => $miembro['id_usuario'],
                'cargo'        => $miembro['cargo'],
                'estado'       => 'activo',
                'fecha_inicio' => date('Y-m-d', strtotime('-1 year')),
                'fecha_fin'    => null,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function recrearActas(int $idCliente): void
    {
        $numeros = ['DEMO-2026-001', 'DEMO-2026-002', 'DEMO-2026-003', 'DEMO-2026-004'];
        $actas = $this->db->table('tbl_actas')
            ->select('id_acta')
            ->where('id_cliente', $idCliente)
            ->whereIn('numero', $numeros)
            ->get()
            ->getResultArray();

        foreach ($actas as $acta) {
            $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actas' . DIRECTORY_SEPARATOR . $acta['id_acta'];
            $this->deleteDirectory($dir);
        }

        $this->db->table('tbl_actas')
            ->where('id_cliente', $idCliente)
            ->whereIn('numero', $numeros)
            ->delete();

        $this->crearActaFirmada($idCliente);
        $this->crearActaPendienteFirma($idCliente);
        $this->crearActaEnEdicion($idCliente);
        $this->crearActaBorrador($idCliente);
    }

    private function crearActaFirmada(int $idCliente): void
    {
        $idActa = $this->insertActa($idCliente, [
            'numero' => 'DEMO-2026-001',
            'consecutivo' => 1,
            'titulo' => 'Reunion ordinaria del consejo - Acta firmada',
            'fecha' => date('Y-m-d', strtotime('-20 days')),
            'hora_inicio' => '18:00:00',
            'hora_fin' => '20:15:00',
            'lugar' => 'Salon social torre A',
            'modalidad' => 'presencial',
            'estado' => 'firmada',
            'codigo_verificacion' => 'CYCLDEM0001',
            'objeto' => 'Revision mensual de administracion, cartera, mantenimiento y decisiones operativas.',
            'orden_dia' => "1. Verificacion del quorum\n2. Informe de administracion\n3. Mantenimiento de zonas comunes\n4. Aprobacion de compromisos\n5. Cierre",
            'desarrollo' => "Se verifica quorum deliberatorio y decisorio. La administracion presenta informe de cartera y mantenimiento. El consejo aprueba priorizar la reparacion de luminarias y solicitar tres cotizaciones para impermeabilizacion.",
            'observaciones' => 'Acta demo con firmas completas para validar PDF, Word y verificacion publica.',
        ]);

        $asistentes = [
            ['user' => 'edison', 'nombre' => 'Edison Cuervo Demo', 'cargo' => 'Presidente del Consejo', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => $this->firmas['edison']],
            ['user' => 'natalia', 'nombre' => 'Natalia Pardo Demo', 'cargo' => 'Consejera', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => $this->firmas['natalia']],
            ['user' => 'carlos', 'nombre' => 'Carlos Mendoza Demo', 'cargo' => 'Consejero', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => $this->firmas['edison']],
            ['user' => 'laura', 'nombre' => 'Laura Torres Demo', 'cargo' => 'Contadora', 'tipo' => 'contador', 'asistencia' => 'asiste', 'firma' => null, 'requiere_firma' => 0],
        ];

        $this->insertAsistentes($idActa, $asistentes, true);
        $this->insertCompromisos($idActa);
        $this->insertVotaciones($idActa);
        $this->insertAnexoDemo($idActa);
    }

    private function crearActaPendienteFirma(int $idCliente): void
    {
        $idActa = $this->insertActa($idCliente, [
            'numero' => 'DEMO-2026-002',
            'consecutivo' => 2,
            'titulo' => 'Reunion extraordinaria - Pendiente de firmas',
            'fecha' => date('Y-m-d', strtotime('-7 days')),
            'hora_inicio' => '19:00:00',
            'hora_fin' => '20:00:00',
            'lugar' => 'Videollamada',
            'modalidad' => 'virtual',
            'estado' => 'pendiente_firma',
            'objeto' => 'Aprobar acciones urgentes de mantenimiento y seguridad.',
            'orden_dia' => "1. Verificacion del quorum\n2. Revision de novedad de seguridad\n3. Aprobacion de acciones urgentes",
            'desarrollo' => 'Se presenta novedad de seguridad y se aprueba reforzar controles de ingreso durante el fin de semana.',
            'observaciones' => 'Acta demo con una firma registrada y dos enlaces pendientes.',
        ]);

        $ids = $this->insertAsistentes($idActa, [
            ['user' => 'edison', 'nombre' => 'Edison Cuervo Demo', 'cargo' => 'Presidente del Consejo', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => $this->firmas['edison']],
            ['user' => 'natalia', 'nombre' => 'Natalia Pardo Demo', 'cargo' => 'Consejera', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
            ['user' => 'carlos', 'nombre' => 'Carlos Mendoza Demo', 'cargo' => 'Consejero', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
        ], false);

        $this->insertCompromisos($idActa);
        $this->insertVotaciones($idActa);

        foreach (['natalia', 'carlos'] as $key) {
            $token = bin2hex(random_bytes(32));
            $this->db->table('tbl_actas_tokens')->insert([
                'token'        => $token,
                'tipo'         => 'firmar_acta',
                'id_acta'      => $idActa,
                'id_asistente' => $ids[$key],
                'id_cliente'   => $idCliente,
                'expires_at'   => date('Y-m-d H:i:s', time() + 15 * 86400),
                'usado_at'     => null,
                'ip_uso'       => null,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            echo 'Firma pendiente DEMO-2026-002 (' . $key . '): ' . base_url('firmar/' . $token) . "\n";
        }
    }

    private function crearActaEnEdicion(int $idCliente): void
    {
        $idActa = $this->insertActa($idCliente, [
            'numero' => 'DEMO-2026-003',
            'consecutivo' => 3,
            'titulo' => 'Acta en edicion - Seguimiento a compromisos',
            'fecha' => date('Y-m-d'),
            'hora_inicio' => '18:30:00',
            'hora_fin' => null,
            'lugar' => 'Administracion',
            'modalidad' => 'mixta',
            'estado' => 'en_edicion',
            'objeto' => 'Seguimiento a compromisos abiertos y preparacion de cierre a firmas.',
            'orden_dia' => "1. Verificacion del quorum\n2. Seguimiento de compromisos\n3. Nuevas solicitudes\n4. Cierre",
            'desarrollo' => 'Borrador avanzado para completar observaciones antes de cerrar a firmas.',
            'observaciones' => 'Acta demo editable.',
        ]);

        $this->insertAsistentes($idActa, [
            ['user' => 'edison', 'nombre' => 'Edison Cuervo Demo', 'cargo' => 'Presidente del Consejo', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
            ['user' => 'natalia', 'nombre' => 'Natalia Pardo Demo', 'cargo' => 'Consejera', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
            ['user' => 'carlos', 'nombre' => 'Carlos Mendoza Demo', 'cargo' => 'Consejero', 'tipo' => 'miembro_consejo', 'asistencia' => 'excusa', 'firma' => null],
        ], false);
        $this->insertCompromisos($idActa);
    }

    private function crearActaBorrador(int $idCliente): void
    {
        $idActa = $this->insertActa($idCliente, [
            'numero' => 'DEMO-2026-004',
            'consecutivo' => 4,
            'titulo' => 'Borrador de reunion proxima',
            'fecha' => date('Y-m-d', strtotime('+10 days')),
            'hora_inicio' => '18:00:00',
            'hora_fin' => null,
            'lugar' => 'Por definir',
            'modalidad' => 'presencial',
            'estado' => 'borrador',
            'objeto' => 'Preparar proxima sesion ordinaria.',
            'orden_dia' => "1. Verificacion del quorum\n2. Lectura de acta anterior\n3. Temas nuevos",
            'desarrollo' => null,
            'observaciones' => 'Borrador demo listo para completar.',
        ]);

        $this->insertAsistentes($idActa, [
            ['user' => 'edison', 'nombre' => 'Edison Cuervo Demo', 'cargo' => 'Presidente del Consejo', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
            ['user' => 'natalia', 'nombre' => 'Natalia Pardo Demo', 'cargo' => 'Consejera', 'tipo' => 'miembro_consejo', 'asistencia' => 'asiste', 'firma' => null],
        ], false);
    }

    private function insertActa(int $idCliente, array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('tbl_actas')->insert($data + [
            'id_cliente' => $idCliente,
            'creada_por' => $this->usuarios['admin'],
            'cerrada_por' => in_array($data['estado'], ['pendiente_firma', 'firmada'], true) ? $this->usuarios['admin'] : null,
            'cerrada_at' => in_array($data['estado'], ['pendiente_firma', 'firmada'], true) ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function insertAsistentes(int $idActa, array $asistentes, bool $actaFirmada): array
    {
        $ids = [];
        foreach ($asistentes as $a) {
            $requiereFirma = (int) ($a['requiere_firma'] ?? 1);
            $firmada = $requiereFirma === 1 && ! empty($a['firma']);
            $firmaEstado = $requiereFirma === 0 ? 'no_requerida' : ($firmada ? 'firmada' : ($a['asistencia'] === 'asiste' ? 'pendiente' : 'ausente'));
            $this->db->table('tbl_acta_asistentes')->insert([
                'id_acta'        => $idActa,
                'id_usuario'     => $this->usuarios[$a['user']] ?? null,
                'nombre'         => $a['nombre'],
                'email'          => $this->emailUsuario($a['user']),
                'telefono'       => '3000000000',
                'cargo'          => $a['cargo'],
                'tipo'           => $a['tipo'],
                'asistencia'     => $a['asistencia'],
                'requiere_firma' => $requiereFirma,
                'firma_estado'   => $actaFirmada && $requiereFirma === 1 ? 'firmada' : $firmaEstado,
                'firma_imagen'   => $firmada || ($actaFirmada && $requiereFirma === 1) ? ($a['firma'] ?: $this->firmas['edison']) : null,
                'firma_ip'       => $firmada || ($actaFirmada && $requiereFirma === 1) ? '127.0.0.1' : null,
                'firma_at'       => $firmada || ($actaFirmada && $requiereFirma === 1) ? date('Y-m-d H:i:s', strtotime('-6 days')) : null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            $ids[$a['user']] = (int) $this->db->insertID();
        }

        return $ids;
    }

    private function insertCompromisos(int $idActa): void
    {
        // [descripcion, nombre, vencimiento, estado, avance, userKey]
        $items = [
            ['Revisar cotizaciones de impermeabilizacion de cubierta.', 'Edison Cuervo Demo', '+7 days', 'en_progreso', 45, 'edison'],
            ['Actualizar matriz de cartera y enviar informe al consejo.', 'Administracion Demo', '+3 days', 'pendiente', 10, 'admin'],
            ['Coordinar jornada de mantenimiento de luminarias.', 'Natalia Pardo Demo', '+12 days', 'pendiente', 0, 'natalia'],
        ];

        foreach ($items as $item) {
            $this->db->table('tbl_acta_compromisos')->insert([
                'id_acta' => $idActa,
                'descripcion' => $item[0],
                'id_responsable' => $this->usuarios[$item[5]] ?? null,
                'responsable_nombre' => $item[1],
                'fecha_vencimiento' => date('Y-m-d', strtotime($item[2])),
                'estado' => $item[3],
                'avance' => $item[4],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function insertVotaciones(int $idActa): void
    {
        $items = [
            ['Aprobacion de mantenimiento de luminarias', 'Se aprueba ejecutar mantenimiento preventivo en zonas comunes.', 3, 0, 0, 'aprobada'],
            ['Cotizacion de impermeabilizacion', 'Se solicita ampliar informacion antes de adjudicar.', 1, 1, 1, 'pendiente'],
        ];

        foreach ($items as $item) {
            $this->db->table('tbl_acta_votaciones')->insert([
                'id_acta' => $idActa,
                'titulo' => $item[0],
                'descripcion' => $item[1],
                'votos_favor' => $item[2],
                'votos_contra' => $item[3],
                'abstenciones' => $item[4],
                'resultado' => $item[5],
                'detalle_votos' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function insertAnexoDemo(int $idActa): void
    {
        $src = FCPATH . 'assets/icons/entrega-04.png';
        if (! is_file($src)) {
            return;
        }

        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actas' . DIRECTORY_SEPARATOR . $idActa;
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = 'anexo-demo-logo.png';
        $dst = $dir . DIRECTORY_SEPARATOR . $name;
        copy($src, $dst);

        $this->db->table('tbl_acta_anexos')->insert([
            'id_acta' => $idActa,
            'nombre' => 'Anexo demo - logo de referencia.png',
            'archivo' => 'uploads/actas/' . $idActa . '/' . $name,
            'mime' => 'image/png',
            'tamano' => filesize($dst),
            'subido_por' => $this->usuarios['admin'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function rolesPorCodigo(): array
    {
        $rows = $this->db->table('tbl_roles')->select('id_rol, codigo')->get()->getResultArray();
        $roles = [];
        foreach ($rows as $row) {
            $roles[$row['codigo']] = (int) $row['id_rol'];
        }

        return $roles;
    }

    private function emailUsuario(string $key): ?string
    {
        $emails = [
            'admin' => 'admin.demo@cycloid.test',
            'edison' => 'edison.demo@cycloid.test',
            'natalia' => 'natalia.demo@cycloid.test',
            'carlos' => 'carlos.demo@cycloid.test',
            'laura' => 'laura.demo@cycloid.test',
        ];

        return $emails[$key] ?? null;
    }

    private function assetDataUri(string $path): string
    {
        if (! is_file($path)) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
    }

    private function storeDemoLogo(): ?string
    {
        $src = FCPATH . 'assets/icons/entrega-04.png';
        if (! is_file($src)) {
            return null;
        }

        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clientes';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $dst = $dir . DIRECTORY_SEPARATOR . 'demo-pwa-logo.png';
        copy($src, $dst);

        return 'uploads/clientes/demo-pwa-logo.png';
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }
            @unlink($path);
        }

        @rmdir($dir);
    }
}
