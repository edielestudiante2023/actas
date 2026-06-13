<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAnexoModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class ActaAnexos extends BaseController
{
    private const EXT = 'pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx';
    private const MAX_KB = 5120;

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaAnexoModel $anexos;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->anexos = new ActaAnexoModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        return view('actas/anexos', [
            'cliente'  => $this->scope->active(),
            'acta'     => $acta,
            'anexos'   => $this->anexos->anexosActa($idActa),
            'editable' => $this->isEditable($acta),
            'maxKb'    => self::MAX_KB,
            'ext'      => self::EXT,
        ]);
    }

    public function create(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/anexos')->with('error', 'Solo puedes adjuntar archivos en actas editables.');
        }

        $rules = [
            'archivo' => 'uploaded[archivo]|max_size[archivo,' . self::MAX_KB . ']|ext_in[archivo,' . self::EXT . ']',
            'nombre'  => 'permit_empty|max_length[200]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('archivo');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Archivo inválido.');
        }

        $stored = $this->store($idActa, $file);
        if ($stored === null) {
            return redirect()->back()->withInput()->with('error', 'No fue posible guardar el archivo.');
        }

        $nombre = trim((string) $this->request->getPost('nombre'));
        if ($nombre === '') {
            $nombre = $file->getClientName();
        }

        $idAnexo = $this->anexos->insert([
            'id_acta'    => $idActa,
            'nombre'     => $nombre,
            'archivo'    => $stored['path'],
            'mime'       => $stored['mime'],
            'tamano'     => $stored['size'],
            'subido_por' => session('id_usuario'),
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        if (! $idAnexo) {
            return redirect()->back()->with('error', 'No fue posible registrar el anexo.');
        }

        $this->auditoria->registrar($idActa, 'crear_anexo', 'Anexo #' . $idAnexo . ' (' . $nombre . ') adjuntado.');

        return redirect()->to('/actas/' . $idActa . '/anexos')->with('success', 'Anexo adjuntado correctamente.');
    }

    public function download(int $idActa, int $idAnexo)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        $anexo = $this->anexos->findForActa($idAnexo, $idActa);
        if ($anexo === null) {
            return redirect()->to('/actas/' . $idActa . '/anexos')->with('error', 'Anexo no encontrado.');
        }

        $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $anexo['archivo']);
        if (! is_file($path)) {
            return redirect()->to('/actas/' . $idActa . '/anexos')->with('error', 'El archivo no existe en el servidor.');
        }

        return $this->response->download($path, null)->setFileName($anexo['nombre']);
    }

    public function delete(int $idActa, int $idAnexo)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/anexos')->with('error', 'Solo puedes eliminar anexos en actas editables.');
        }

        $anexo = $this->anexos->findForActa($idAnexo, $idActa);
        if ($anexo === null) {
            return redirect()->to('/actas/' . $idActa . '/anexos')->with('error', 'Anexo no encontrado.');
        }

        $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $anexo['archivo']);
        if (is_file($path)) {
            @unlink($path);
        }
        $this->anexos->delete($idAnexo);
        $this->auditoria->registrar($idActa, 'eliminar_anexo', 'Anexo #' . $idAnexo . ' eliminado.');

        return redirect()->to('/actas/' . $idActa . '/anexos')->with('success', 'Anexo eliminado.');
    }

    /**
     * @return array{path:string,mime:string,size:int}|null
     */
    private function store(int $idActa, UploadedFile $file): ?array
    {
        $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actas' . DIRECTORY_SEPARATOR . $idActa;
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        $mime = $file->getMimeType();
        $size = (int) $file->getSize();
        $newName = $file->getRandomName();
        $file->move($directory, $newName, true);

        return [
            'path' => 'uploads/actas/' . $idActa . '/' . $newName,
            'mime' => $mime,
            'size' => $size,
        ];
    }

    private function actaContext(int $idActa): ?array
    {
        $this->scope->syncActiveSession();
        $idCliente = $this->scope->activeId();
        if ($idCliente === null) {
            return null;
        }

        return $this->actas->findForCliente($idActa, $idCliente);
    }

    private function isEditable(array $acta): bool
    {
        return in_array($acta['estado'], ['borrador', 'en_edicion'], true);
    }
}
