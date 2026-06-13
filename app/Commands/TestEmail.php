<?php

namespace App\Commands;

use App\Libraries\EmailService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class TestEmail extends BaseCommand
{
    protected $group       = 'Actas';
    protected $name        = 'test:email';
    protected $description = 'Envía un correo de prueba usando la configuración SendGrid.';
    protected $usage       = 'test:email correo@dominio.com';

    public function run(array $params)
    {
        $to = trim((string) ($params[0] ?? ''));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Uso: php spark test:email correo@dominio.com');

            return EXIT_ERROR;
        }

        $html = view('emails/firma_enlace', [
            'nombre'       => 'Prueba',
            'cliente'      => ['nombre' => 'Actas'],
            'acta'         => ['numero' => 'TEST', 'titulo' => 'Correo de prueba'],
            'firmaUrl'     => base_url('login'),
            'expira'       => null,
            'esPrueba'     => true,
        ]);

        try {
            (new EmailService())->sendHtml($to, 'Prueba', 'Prueba de correo - Actas', $html);
        } catch (Throwable $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write('Correo de prueba enviado a ' . $to, 'green');

        return EXIT_SUCCESS;
    }
}
