<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ClienteModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Clientes extends BaseController
{
    private ClienteModel $clientes;
    private ClienteScope $scope;

    public function __construct()
    {
        $this->clientes = new ClienteModel();
        $this->scope = new ClienteScope();
    }

    public function index()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para administrar clientes.');
        }

        $clientes = $this->clientes
            ->orderBy('estado', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return view('clientes/index', ['clientes' => $clientes]);
    }

    public function createForm()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para crear clientes.');
        }

        return view('clientes/form', [
            'cliente' => null,
            'action'  => base_url('clientes'),
            'method'  => 'post',
        ]);
    }

    public function create()
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para crear clientes.');
        }

        $rules = $this->rules();
        $rules['logo'] = [
            'label' => 'Logo',
            'rules' => 'permit_empty|is_image[logo]|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idCliente = $this->clientes->insert($this->payload(), true);
        if (! $idCliente) {
            return redirect()->back()->withInput()->with('error', 'No fue posible crear el cliente.');
        }

        $logo = $this->storeLogo((int) $idCliente, $this->request->getFile('logo'));
        if ($logo !== null) {
            $this->clientes->update($idCliente, ['logo' => $logo]);
        }

        return redirect()->to('/clientes')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(int $idCliente)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para editar clientes.');
        }

        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado.');
        }

        return view('clientes/form', [
            'cliente' => $cliente,
            'action'  => base_url('clientes/' . $idCliente),
            'method'  => 'post',
        ]);
    }

    public function update(int $idCliente)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para editar clientes.');
        }

        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado.');
        }

        $rules = $this->rules();
        $rules['logo'] = [
            'label' => 'Logo',
            'rules' => 'permit_empty|is_image[logo]|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();
        $logo = $this->storeLogo($idCliente, $this->request->getFile('logo'), $cliente['logo'] ?? null);
        if ($logo !== null) {
            $data['logo'] = $logo;
        }

        $this->clientes->update($idCliente, $data);
        if ($this->scope->activeId() === $idCliente) {
            $this->scope->setActive($idCliente);
        }

        return redirect()->to('/clientes')->with('success', 'Cliente actualizado correctamente.');
    }

    public function deactivate(int $idCliente)
    {
        if (! $this->requireSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para cambiar clientes.');
        }

        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado.');
        }

        $nuevoEstado = ($cliente['estado'] ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $this->clientes->update($idCliente, ['estado' => $nuevoEstado]);
        if ($nuevoEstado === 'inactivo' && $this->scope->activeId() === $idCliente) {
            $this->scope->clearActive();
        }

        return redirect()->to('/clientes')->with('success', 'Estado del cliente actualizado.');
    }

    public function setActive()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');

        if ($idCliente <= 0) {
            $this->scope->clearActive();

            return redirect()->to('/dashboard')->with('success', 'Cliente activo limpiado.');
        }

        $cliente = $this->scope->setActive($idCliente);
        if ($cliente === null) {
            return redirect()->to('/dashboard')->with('error', 'No tienes acceso a ese cliente o está inactivo.');
        }

        return redirect()->to('/dashboard')->with('success', 'Cliente activo: ' . $cliente['nombre']);
    }

    public function logo(int $idCliente)
    {
        $cliente = $this->clientes->find($idCliente);
        if ($cliente === null || empty($cliente['logo']) || ! $this->scope->canAccess($idCliente)) {
            return $this->response->setStatusCode(404);
        }

        $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cliente['logo']);
        if (! is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            'gif'         => 'image/gif',
            'png'         => 'image/png',
            default       => 'application/octet-stream',
        };

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($path));
    }

    private function rules(): array
    {
        return [
            'nombre' => 'required|min_length[3]|max_length[200]',
            'nit' => 'permit_empty|max_length[30]',
            'direccion' => 'permit_empty|max_length[200]',
            'ciudad' => 'permit_empty|max_length[100]',
            'telefono' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[150]',
            'estado' => 'required|in_list[activo,inactivo]',
        ];
    }

    private function payload(): array
    {
        return [
            'nombre'    => trim((string) $this->request->getPost('nombre')),
            'nit'       => $this->nullablePost('nit'),
            'direccion' => $this->nullablePost('direccion'),
            'ciudad'    => $this->nullablePost('ciudad'),
            'telefono'  => $this->nullablePost('telefono'),
            'email'     => $this->nullablePost('email'),
            'estado'    => (string) $this->request->getPost('estado'),
        ];
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function storeLogo(int $idCliente, ?UploadedFile $file, ?string $currentLogo = null): ?string
    {
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $extension = strtolower($file->getClientExtension());
        $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clientes' . DIRECTORY_SEPARATOR . $idCliente;
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if ($currentLogo !== null) {
            $currentPath = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $currentLogo);
            if (is_file($currentPath)) {
                unlink($currentPath);
            }
        }

        $fileName = 'logo.' . $extension;
        $file->move($directory, $fileName, true);

        return 'uploads/clientes/' . $idCliente . '/' . $fileName;
    }

    private function requireSuperadmin(): bool
    {
        return (bool) session('es_superadmin');
    }

}
