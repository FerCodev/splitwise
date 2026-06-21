<?php

namespace App\Controllers;

use App\Models\UserPaymentMethod;

class MediosCobro extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');
        $model = new UserPaymentMethod();
        $medios = $model->where('user_id', $userId)->orderBy('favorito', 'DESC')->orderBy('nombre', 'ASC')->findAll();

        return view('mis-medios/index', [
            'medios' => $medios,
        ]);
    }

    public function new()
    {
        return view('mis-medios/form');
    }

    public function create()
    {
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
            'titular' => 'required|max_length[255]',
            'alias' => 'permit_empty|max_length[255]',
            'cbu_cvu' => 'permit_empty|max_length[255]',
            'banco' => 'permit_empty|max_length[255]',
            'payment_link' => 'permit_empty|max_length[500]|valid_url_strict',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alias = $this->request->getPost('alias');
        $cbuCvu = $this->request->getPost('cbu_cvu');
        $paymentLink = $this->request->getPost('payment_link');

        if (empty($alias) && empty($cbuCvu)) {
            return redirect()->back()->withInput()->with('errors', [
                'alias' => 'Debe proporcionar al menos un Alias o CBU/CVU.',
            ]);
        }

        $model = new UserPaymentMethod();
        $model->insert([
            'user_id' => session()->get('userId'),
            'tipo' => $this->request->getPost('tipo') ?: 'otro',
            'nombre' => $this->request->getPost('nombre'),
            'alias' => $alias,
            'cbu_cvu' => $cbuCvu,
            'banco' => $this->request->getPost('banco'),
            'titular' => $this->request->getPost('titular'),
            'payment_link' => $paymentLink,
            'activo' => 1,
            'favorito' => 0,
        ]);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Medio de cobro creado correctamente.');
    }

    public function edit(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== (int) session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio no encontrado.');
        }

        return view('mis-medios/form', [
            'medio' => $medio,
        ]);
    }

    public function update(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== (int) session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio no encontrado.');
        }

        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
            'titular' => 'required|max_length[255]',
            'alias' => 'permit_empty|max_length[255]',
            'cbu_cvu' => 'permit_empty|max_length[255]',
            'banco' => 'permit_empty|max_length[255]',
            'payment_link' => 'permit_empty|max_length[500]|valid_url_strict',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alias = $this->request->getPost('alias');
        $cbuCvu = $this->request->getPost('cbu_cvu');
        $paymentLink = $this->request->getPost('payment_link');

        if (empty($alias) && empty($cbuCvu)) {
            return redirect()->back()->withInput()->with('errors', [
                'alias' => 'Debe proporcionar al menos un Alias o CBU/CVU.',
            ]);
        }

        $model->update($id, [
            'tipo' => $this->request->getPost('tipo') ?: 'otro',
            'nombre' => $this->request->getPost('nombre'),
            'alias' => $alias,
            'cbu_cvu' => $cbuCvu,
            'banco' => $this->request->getPost('banco'),
            'titular' => $this->request->getPost('titular'),
            'payment_link' => $paymentLink,
        ]);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Medio de cobro actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== (int) session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio no encontrado.');
        }

        $model->delete($id);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Medio de cobro eliminado correctamente.');
    }

    public function toggle(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== (int) session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio no encontrado.');
        }

        $model->update($id, ['activo' => $medio['activo'] ? 0 : 1]);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Estado actualizado.');
    }

    public function favorito(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);
        $userId = (int) session()->get('userId');

        if (!$medio || (int) $medio['user_id'] !== $userId) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio no encontrado.');
        }

        if ((int) $medio['favorito']) {
            $model->quitarFavorito($id, $userId);

            return redirect()->to('/mis-medios-de-cobro')->with('success', 'Favorito quitado correctamente.');
        }

        if (!(int) $medio['activo']) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Solo se puede marcar como favorito un medio activo.');
        }

        $model->marcarFavorito($id, $userId);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Favorito actualizado correctamente.');
    }
}
