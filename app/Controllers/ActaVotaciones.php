<?php

namespace App\Controllers;

use App\Libraries\ClienteScope;
use App\Models\ActaAsistenteModel;
use App\Models\ActaAuditoriaModel;
use App\Models\ActaModel;
use App\Models\ActaVotacionModel;
use App\Models\ActaVotacionVotoModel;

class ActaVotaciones extends BaseController
{
    private const RESULTADOS = ['pendiente', 'aprobada', 'rechazada'];
    private const VOTOS      = ['favor', 'contra', 'abstencion'];

    private ClienteScope $scope;
    private ActaModel $actas;
    private ActaVotacionModel $votaciones;
    private ActaVotacionVotoModel $votos;
    private ActaAsistenteModel $asistentes;
    private ActaAuditoriaModel $auditoria;

    public function __construct()
    {
        $this->scope = new ClienteScope();
        $this->actas = new ActaModel();
        $this->votaciones = new ActaVotacionModel();
        $this->votos = new ActaVotacionVotoModel();
        $this->asistentes = new ActaAsistenteModel();
        $this->auditoria = new ActaAuditoriaModel();
    }

    public function index(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        // Asistente que corresponde al usuario en sesión (para votar en-app).
        $miAsistente = $this->asistentes->asistenteDeUsuario($idActa, (int) session('id_usuario'));

        $votaciones = $this->votaciones->votacionesActa($idActa);
        foreach ($votaciones as &$v) {
            if ($v['modo'] === 'digital') {
                $v['_conteo'] = $this->votos->conteo((int) $v['id_votacion']);
                $v['_mi_voto'] = $miAsistente !== null ? $this->votos->miVoto((int) $v['id_votacion'], (int) $miAsistente['id_asistente']) : null;
            }
        }
        unset($v);

        return view('actas/votaciones', [
            'cliente'      => $this->scope->active(),
            'acta'         => $acta,
            'votaciones'   => $votaciones,
            'resultados'   => self::RESULTADOS,
            'editable'     => $this->isEditable($acta),
            'miAsistente'  => $miAsistente, // null si el usuario no es asistente de esta acta
        ]);
    }

    /** Crea una votación digital "abierta" para votar en vivo. */
    public function abrir(int $idActa)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }
        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Solo puedes abrir votaciones en actas editables.');
        }

        if (! $this->validate(['titulo' => 'required|min_length[3]|max_length[200]', 'descripcion' => 'permit_empty'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idVotacion = $this->votaciones->insert([
            'id_acta'      => $idActa,
            'titulo'       => trim((string) $this->request->getPost('titulo')),
            'descripcion'  => $this->nullablePost('descripcion'),
            'votos_favor'  => 0,
            'votos_contra' => 0,
            'abstenciones' => 0,
            'resultado'    => 'pendiente',
            'estado'       => 'abierta',
            'modo'         => 'digital',
        ], true);

        if (! $idVotacion) {
            return redirect()->back()->withInput()->with('error', 'No fue posible abrir la votación.');
        }

        $this->auditoria->registrar($idActa, 'abrir_votacion', 'Votación digital #' . $idVotacion . ' abierta.');

        return redirect()->to('/actas/' . $idActa . '/votaciones')->with('success', 'Votación abierta. Los consejeros ya pueden votar.');
    }

    /** Voto en-app del consejero en sesión. */
    public function votar(int $idActa, int $idVotacion)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }

        $votacion = $this->votaciones->findForActa($idVotacion, $idActa);
        if ($votacion === null || $votacion['estado'] !== 'abierta' || $votacion['modo'] !== 'digital') {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'La votación no está abierta.');
        }

        $asistente = $this->asistentes->asistenteDeUsuario($idActa, (int) session('id_usuario'));
        if ($asistente === null || $asistente['asistencia'] !== 'asiste') {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Solo los asistentes presentes pueden votar.');
        }

        $voto = (string) $this->request->getPost('voto');
        if (! in_array($voto, self::VOTOS, true)) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Opción de voto inválida.');
        }

        $this->votos->registrar($idVotacion, (int) $asistente['id_asistente'], $voto, (string) $this->request->getIPAddress());

        return redirect()->to('/actas/' . $idActa . '/votaciones')->with('success', 'Tu voto quedó registrado.');
    }

    /** Cierra la votación digital y calcula el resultado. */
    public function cerrar(int $idActa, int $idVotacion)
    {
        $acta = $this->actaContext($idActa);
        if ($acta === null) {
            return redirect()->to('/actas')->with('error', 'Acta no encontrada para el cliente activo.');
        }
        if (! $this->isEditable($acta)) {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'Solo puedes cerrar votaciones en actas editables.');
        }

        $votacion = $this->votaciones->findForActa($idVotacion, $idActa);
        if ($votacion === null || $votacion['estado'] !== 'abierta') {
            return redirect()->to('/actas/' . $idActa . '/votaciones')->with('error', 'La votación no está abierta.');
        }

        $c = $this->votos->conteo($idVotacion);
        $this->votaciones->update($idVotacion, [
            'votos_favor'  => $c['favor'],
            'votos_contra' => $c['contra'],
            'abstenciones' => $c['abstencion'],
            'resultado'    => $this->computeResultado($c['favor'], $c['contra']),
            'estado'       => 'cerrada',
        ]);

        $this->auditoria->registrar($idActa, 'cerrar_votacion', 'Votación #' . $idVotacion . ' cerrada (' . $c['favor'] . '-' . $c['contra'] . '-' . $c['abstencion'] . ').');

        return redirect()->to('/actas/' . $idActa . '/votaciones')->with('success', 'Votación cerrada.');
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
            'estado'       => 'cerrada',
            'modo'         => 'manual',
        ], true);

        if (! $idVotacion) {
            return redirect()->back()->withInput()->with('error', 'No fue posible registrar la votación.');
        }

        $this->auditoria->registrar($idActa, 'crear_votacion', 'Votación #' . $idVotacion . ' registrada (manual).');

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
