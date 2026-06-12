<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            ['codigo' => 'superadmin',         'nombre' => 'Super Administrador',      'descripcion' => 'Administrador de la plataforma. Acceso global a todos los clientes.', 'nivel' => 100],
            ['codigo' => 'administrador',       'nombre' => 'Administrador',            'descripcion' => 'Administrador del cliente de propiedad horizontal.',                  'nivel' => 80],
            ['codigo' => 'presidente_consejo',  'nombre' => 'Presidente del Consejo',   'descripcion' => 'Preside el consejo de administracion.',                               'nivel' => 70],
            ['codigo' => 'consejero',           'nombre' => 'Consejero',                'descripcion' => 'Miembro del consejo de administracion.',                              'nivel' => 60],
            ['codigo' => 'revisor_fiscal',      'nombre' => 'Revisor Fiscal',           'descripcion' => 'Revisor fiscal del cliente.',                                        'nivel' => 50],
            ['codigo' => 'contador',            'nombre' => 'Contador',                 'descripcion' => 'Contador del cliente.',                                              'nivel' => 40],
            ['codigo' => 'abogado',             'nombre' => 'Abogado',                  'descripcion' => 'Asesor juridico del cliente.',                                       'nivel' => 40],
        ];

        $builder = $this->db->table('tbl_roles');

        foreach ($roles as $rol) {
            // Idempotente: solo inserta si el codigo no existe.
            $existe = $builder->where('codigo', $rol['codigo'])->countAllResults(false);
            $builder->resetQuery();

            if ($existe == 0) {
                $rol['activo']     = 1;
                $rol['created_at'] = $now;
                $builder->insert($rol);
                echo "Rol creado: {$rol['codigo']}\n";
            } else {
                echo "Rol ya existe (omitido): {$rol['codigo']}\n";
            }
        }
    }
}
