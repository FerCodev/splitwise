<?php

namespace App\Controllers;

use App\Models\Categoria;

class Categorias extends BaseController
{
    public function index()
    {
        $model = new Categoria();
        $categorias = $model->orderBy('id', 'ASC')->paginate(10);
        $pager = $model->pager;

        $gastoModel = model('App\Models\Gasto');
        $usadas = [];
        foreach ($categorias as &$c) {
            $id = (int) $c['id'];
            $usadas[$id] = $gastoModel->where('categoria_id', $id)->countAllResults();
        }

        return view('categorias/index', [
            'categorias' => $categorias,
            'usadas' => $usadas,
            'pager' => $pager,
            'protegida' => Categoria::PROTEGIDA,
        ]);
    }

    public function new()
    {
        return view('categorias/form');
    }

    public function create()
    {
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[50]|is_unique[categorias.nombre]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new Categoria();
        $model->insert([
            'nombre' => $this->request->getPost('nombre'),
            'activa' => 1,
        ]);

        return redirect()->to('/categorias')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(int $id)
    {
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            return redirect()->to('/categorias')->with('error', 'Categoría no encontrada.');
        }

        if (Categoria::isProtegida($categoria['nombre'])) {
            return redirect()->to('/categorias')->with('error', 'No se puede editar la categoría "Otros".');
        }

        return view('categorias/form', ['categoria' => $categoria]);
    }

    public function update(int $id)
    {
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            return redirect()->to('/categorias')->with('error', 'Categoría no encontrada.');
        }

        if (Categoria::isProtegida($categoria['nombre'])) {
            return redirect()->to('/categorias')->with('error', 'No se puede editar la categoría "Otros".');
        }

        $rules = [
            'nombre' => "required|min_length[2]|max_length[50]|is_unique[categorias.nombre,id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, ['nombre' => $this->request->getPost('nombre')]);

        return redirect()->to('/categorias')->with('success', 'Categoría actualizada correctamente.');
    }

    public function toggle(int $id)
    {
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            return redirect()->to('/categorias')->with('error', 'Categoría no encontrada.');
        }

        if (Categoria::isProtegida($categoria['nombre'])) {
            return redirect()->to('/categorias')->with('error', 'No se puede desactivar la categoría "Otros".');
        }

        $nuevoEstado = (int) $categoria['activa'] ? 0 : 1;
        $model->update($id, ['activa' => $nuevoEstado]);

        $mensaje = $nuevoEstado
            ? 'Categoría activada correctamente.'
            : 'Categoría desactivada correctamente.';

        return redirect()->to('/categorias')->with('success', $mensaje);
    }

    public function delete(int $id)
    {
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            return redirect()->to('/categorias')->with('error', 'Categoría no encontrada.');
        }

        if (Categoria::isProtegida($categoria['nombre'])) {
            return redirect()->to('/categorias')->with('error', 'No se puede eliminar la categoría "Otros".');
        }

        if ($model->esUsadaPorGastos($id)) {
            return redirect()->to('/categorias')->with('error', 'No se puede eliminar la categoría porque tiene gastitos asociados.');
        }

        $model->delete($id);

        return redirect()->to('/categorias')->with('success', 'Categoría eliminada correctamente.');
    }
}
