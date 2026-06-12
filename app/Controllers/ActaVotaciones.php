<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaVotacionModel;

class ActaVotaciones extends BaseController
{
    private const RESULTADOS = ['pendiente', 'aprobada', 'rechazada'];

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaVotacionModel $votaciones;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->votaciones = new ActaVotacionModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        return view('actas/votaciones', [
            'cliente'    => $this->scope->active(),
            'acta'       => $acta,
            'votaciones' => $this->votaciones->votacionesActa($idActa),
            'resultados' => self::RESULTADOS,
            'editable'   => $this->isEditable($acta),
        ]);
    }

    public function create(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Solo puedes registrar votaciones en actas editables.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $favor  = (int) $this->request->getPost('votos_favor');
        $contra = (int) $this->request->getPost('votos_contra');
        $abst   = (int) $this->request->getPost('abstenciones');

        $idVotacion = $this->votaciones->insert([
            'id_acta'      => $idActa,
            'titulo'       => trim((string) $this->request->getPost('titulo')),
            'descripcion'  => $this->nullablePost('descripcion'),
            'votos_favor'  => $favor,
            'votos_contra' => $contra,
            'abstenciones' => $abst,
            'resultado'    => $this->computeResultado($favor, $contra),
        ], true);

        if (! $idVotacion) {
            return redirect()->back()->withInput()->with('error', 'No fue posible registrar la votación.');
        }

        $this->auditoria->registrar($idActa, 'crear_votacion', 'Votación #' . $idVotacion . ' registrada.');

        return redirect()->to('/actas/' . $idActa . '/votaciones')->with('success', 'Votación registrada correctamente.');
    }

    public function update(int $idActa, int $idVotacion)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Solo puedes actualizar votaciones en actas editables.');
        }

        $votacion = $this->votaciones->findForActa($idVotacion, $idActa);
        if ($votacion === null) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Votación no encontrada.');
        }

        $rules = [
            'votos_favor'  => 'required|is_natural',
            'votos_contra' => 'required|is_natural',
            'abstenciones' => 'required|is_natural',
            'resultado'    => 'permit_empty|in_list[' . implode(',', self::RESULTADOS) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $favor  = (int) $this->request->getPost('votos_favor');
        $contra = (int) $this->request->getPost('votos_contra');
        $abst   = (int) $this->request->getPost('abstenciones');
        // Si no se fuerza un resultado manual (desempate), se calcula por mayoría.
        $resultado = $this->nullablePost('resultado') ?? $this->computeResultado($favor, $contra);

        $this->votaciones->update($idVotacion, [
            'votos_favor'  => $favor,
            'votos_contra' => $contra,
            'abstenciones' => $abst,
            'resultado'    => $resultado,
        ]);

        $this->auditoria->registrar($idActa, 'actualizar_votacion', 'Votación #' . $idVotacion . ': ' . $resultado . '.');

        return redirect()->to('/actas/' . $idActa . '/votaciones')->with('success', 'Votación actualizada.');
    }

    private function rules(): array
    {
        return [
            'titulo'       => 'required|min_length[3]|max_length[200]',
            'descripcion'  => 'permit_empty',
            'votos_favor'  => 'required|is_natural',
            'votos_contra' => 'required|is_natural',
            'abstenciones' => 'required|is_natural',
        ];
    }

    /**
     * Resultado por mayoría simple. Empate (incl. 0-0) queda 'pendiente' (requiere desempate manual).
     */
    private function computeResultado(int $favor, int $contra): string
    {
        if ($favor > $contra) {
            return 'aprobada';
        }
        if ($contra > $favor) {
            return 'rechazada';
        }

        return 'pendiente';
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
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
