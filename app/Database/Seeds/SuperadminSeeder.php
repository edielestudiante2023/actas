<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run()
    {
        $now   = date('Y-m-d H:i:s');
        $email = 'edielestudiante2023@gmail.com';

        $usuariosTable = $this->db->table('tbl_usuarios');

        // Idempotente: no duplicar el superadmin si ya existe.
        $usuario = $usuariosTable->where('email', $email)->get()->getRowArray();

        if ($usuario === null) {
            // La contrasena se lee del .env (no se commitea). Fallback solo para dev.
            $passwordPlano = env('superadmin.password', 'actas123');

            $usuariosTable->insert([
                'nombre_completo'  => 'Edison Cuervo',
                'tipo_documento'   => 'CC',
                'numero_documento' => '123456',
                'email'            => $email,
                'telefono'         => null,
                'password'         => password_hash($passwordPlano, PASSWORD_BCRYPT),
                'estado'           => 'activo',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $idUsuario = $this->db->insertID();
            echo "Superadmin creado (id={$idUsuario}): {$email}\n";
        } else {
            $idUsuario = $usuario['id_usuario'];
            echo "Superadmin ya existe (id={$idUsuario}): {$email}\n";
        }

        // Rol superadmin (id_conjunto = NULL => plataforma global).
        $rol = $this->db->table('tbl_roles')->where('codigo', 'superadmin')->get()->getRowArray();
        if ($rol === null) {
            echo "ERROR: no existe el rol 'superadmin'. Corre primero RolesSeeder.\n";
            return;
        }
        $idRol = $rol['id_rol'];

        $usuarioRolTable = $this->db->table('tbl_usuario_rol');
        $existeAsignacion = $usuarioRolTable
            ->where('id_usuario', $idUsuario)
            ->where('id_rol', $idRol)
            ->where('id_conjunto', null)
            ->countAllResults();

        if ($existeAsignacion == 0) {
            $usuarioRolTable->insert([
                'id_usuario'  => $idUsuario,
                'id_rol'      => $idRol,
                'id_conjunto' => null,
                'estado'      => 'activo',
                'created_at'  => $now,
            ]);
            echo "Asignacion superadmin -> plataforma creada.\n";
        } else {
            echo "Asignacion superadmin -> plataforma ya existe (omitida).\n";
        }
    }
}
