<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\Grupo;
use App\Models\GastoParticipante;

class Gastos extends BaseController
{
    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        $gastoModel = new Gasto();
        $filters = [];

        if ($this->request->getGet('grupo_id')) {
            $filters['grupo_id'] = (int) $this->request->getGet('grupo_id');
        }
        if ($this->request->getGet('pagador_id')) {
            $filters['pagador_id'] = (int) $this->request->getGet('pagador_id');
        }
        if ($this->request->getGet('fecha_desde')) {
            $filters['fecha_desde'] = $this->request->getGet('fecha_desde');
        }
        if ($this->request->getGet('fecha_hasta')) {
            $filters['fecha_hasta'] = $this->request->getGet('fecha_hasta');
        }
        if ($this->request->getGet('descripcion')) {
            $filters['descripcion'] = $this->request->getGet('descripcion');
        }
        $filters['sort'] = $this->request->getGet('sort') ?: 'fecha';
        $filters['order'] = $this->request->getGet('order') ?: 'DESC';

        $gastos = $gastoModel->getGastosWithFilters($filters);

        return view('gastos/index', [
            'gastos' => $gastos,
            'grupos' => $grupos,
            'filters' => $filters,
            'categorias' => Gasto::categoriasPermitidas(),
        ]);
    }

    public function new()
    {
        $grupoId = $this->request->getGet('grupo_id') ?: old('grupo_id');
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));
        $miembros = [];

        if ($grupoId) {
            $acceso = $this->verificarAccesoGrupo((int) $grupoId);
            if ($acceso === null) {
                return redirect()->to('/gastos')->with('error', 'Grupo no encontrado o no tenés acceso.');
            }

            $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'gasto_create');
            if ($bloqueo) {
                return redirect()->to('/gastos')->with('error', $bloqueo);
            }

            $miembros = $grupoModel->getMiembros((int) $grupoId);
        }

        return view('gastos/form', [
            'grupos' => $grupos,
            'grupoId' => $grupoId,
            'miembros' => $miembros,
            'categorias' => Gasto::categoriasPermitidas(),
        ]);
    }

    public function create()
    {
        $rules = [
            'descripcion' => 'required|min_length[2]|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'grupo_id' => 'required|is_natural_no_zero',
            'pagador_id' => 'required|is_natural_no_zero',
            'participantes' => 'required',
            'categoria' => 'permit_empty|in_list[' . implode(',', Gasto::categoriasPermitidas()) . ']',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $grupoId = (int) $this->request->getPost('grupo_id');
        $pagadorId = (int) $this->request->getPost('pagador_id');
        $monto = (float) $this->request->getPost('monto');
        $participantesIds = $this->request->getPost('participantes');

        if (!is_array($participantesIds) || count($participantesIds) < 1) {
            return redirect()->back()->withInput()->with('errors', ['participantes' => 'Debe seleccionar al menos un participante.']);
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo || !$grupoModel->isMiembro($grupoId, $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($grupo['estado'], 'gasto_create');
        if ($bloqueo) {
            return redirect()->to('/gastos')->with('error', $bloqueo);
        }

        if (!$grupoModel->isMiembro($grupoId, $pagadorId)) {
            return redirect()->back()->withInput()->with('errors', ['pagador_id' => 'El pagador no pertenece al grupo.']);
        }

        $miembros = $grupoModel->getMiembros($grupoId);
        $miembrosIds = array_column($miembros, 'user_id');

        foreach ($participantesIds as $pid) {
            if (!in_array((int) $pid, $miembrosIds)) {
                return redirect()->back()->withInput()->with('errors', ['participantes' => 'Uno de los participantes no pertenece al grupo.']);
            }
        }

        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $porcion = round($monto / count($participantesIds), 2);

        $diferencias = round($monto - ($porcion * count($participantesIds)), 2);

        $gastoModel = new Gasto();
        $categoria = $this->request->getPost('categoria');
        if (empty($categoria) || !in_array($categoria, Gasto::categoriasPermitidas())) {
            $categoria = 'Otros';
        }

        $gastoId = $gastoModel->insert([
            'grupo_id' => $grupoId,
            'pagador_id' => $pagadorId,
            'descripcion' => $this->request->getPost('descripcion'),
            'monto' => $monto,
            'fecha' => $this->request->getPost('fecha'),
            'categoria' => $categoria,
        ]);

        $participanteModel = new GastoParticipante();
        foreach ($participantesIds as $i => $pid) {
            $asignado = $porcion;
            if ($i === array_key_last($participantesIds)) {
                $asignado += $diferencias;
            }
            $participanteModel->insert([
                'gasto_id' => $gastoId,
                'user_id' => $pid,
                'monto_asignado' => round($asignado, 2),
            ]);
        }

        return redirect()->to('/gastos')->with('success', 'Gasto creado correctamente.');
    }

    public function show(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->select('gastos.*, users.name as pagador_nombre, grupos.nombre as grupo_nombre')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
        }

        $participantes = $gastoModel->getParticipantes($id);

        return view('gastos/show', [
            'gasto' => $gasto,
            'participantes' => $participantes,
            'categorias' => Gasto::categoriasPermitidas(),
        ]);
    }

    public function edit(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
        }

        $grupos = $grupoModel->getGruposByUser($userId);
        $miembros = $grupoModel->getMiembros($gasto['grupo_id']);
        $participantes = $gastoModel->getParticipantes($id);
        $participantesIds = array_column($participantes, 'user_id');

        return view('gastos/form', [
            'gasto' => $gasto,
            'grupos' => $grupos,
            'grupoId' => $gasto['grupo_id'],
            'miembros' => $miembros,
            'participantesIds' => $participantesIds,
            'categorias' => Gasto::categoriasPermitidas(),
        ]);
    }

    public function update(int $id)
    {
        $gastoModel = new Gasto();
        $gastoExistente = $gastoModel->find($id);

        if (!$gastoExistente) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $rules = [
            'descripcion' => 'required|min_length[2]|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'grupo_id' => 'required|is_natural_no_zero',
            'pagador_id' => 'required|is_natural_no_zero',
            'participantes' => 'required',
            'categoria' => 'permit_empty|in_list[' . implode(',', Gasto::categoriasPermitidas()) . ']',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $grupoId = (int) $this->request->getPost('grupo_id');
        $pagadorId = (int) $this->request->getPost('pagador_id');
        $monto = (float) $this->request->getPost('monto');
        $participantesIds = $this->request->getPost('participantes');

        if (!is_array($participantesIds) || count($participantesIds) < 1) {
            return redirect()->back()->withInput()->with('errors', ['participantes' => 'Debe seleccionar al menos un participante.']);
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo || !$grupoModel->isMiembro($grupoId, $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($grupo['estado'], 'gasto_edit');
        if ($bloqueo) {
            return redirect()->to('/gastos')->with('error', $bloqueo);
        }

        if (!$grupoModel->isMiembro($grupoId, $pagadorId)) {
            return redirect()->back()->withInput()->with('errors', ['pagador_id' => 'El pagador no pertenece al grupo.']);
        }

        $miembros = $grupoModel->getMiembros($grupoId);
        $miembrosIds = array_column($miembros, 'user_id');

        foreach ($participantesIds as $pid) {
            if (!in_array((int) $pid, $miembrosIds)) {
                return redirect()->back()->withInput()->with('errors', ['participantes' => 'Uno de los participantes no pertenece al grupo.']);
            }
        }

        $categoria = $this->request->getPost('categoria');
        if (empty($categoria) || !in_array($categoria, Gasto::categoriasPermitidas())) {
            $categoria = 'Otros';
        }

        $gastoModel->update($id, [
            'grupo_id' => $grupoId,
            'pagador_id' => $pagadorId,
            'descripcion' => $this->request->getPost('descripcion'),
            'monto' => $monto,
            'fecha' => $this->request->getPost('fecha'),
            'categoria' => $categoria,
        ]);

        $participanteModel = new GastoParticipante();
        $participanteModel->where('gasto_id', $id)->delete();

        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $porcion = round($monto / count($participantesIds), 2);
        $diferencias = round($monto - ($porcion * count($participantesIds)), 2);

        foreach ($participantesIds as $i => $pid) {
            $asignado = $porcion;
            if ($i === array_key_last($participantesIds)) {
                $asignado += $diferencias;
            }
            $participanteModel->insert([
                'gasto_id' => $id,
                'user_id' => $pid,
                'monto_asignado' => round($asignado, 2),
            ]);
        }

        return redirect()->to('/gastos')->with('success', 'Gasto actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($gasto['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($gasto['grupo_id'], session()->get('userId'))) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
        }

        $bloqueo = Grupo::restriccionEstado($grupo['estado'], 'gasto_delete');
        if ($bloqueo) {
            return redirect()->to('/gastos')->with('error', $bloqueo);
        }

        $gastoModel->delete($id);

        return redirect()->to('/gastos')->with('success', 'Gasto eliminado correctamente.');
    }

    private function verificarAccesoGrupo(int $grupoId): ?array
    {
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo) {
            return null;
        }

        $userId = session()->get('userId');
        if (!$grupoModel->isMiembro($grupoId, $userId)) {
            return null;
        }

        return [
            'grupo' => $grupo,
            'rol' => $grupoModel->getUserRol($grupoId, $userId),
        ];
    }
}
