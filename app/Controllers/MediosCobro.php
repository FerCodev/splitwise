<?php

namespace App\Controllers;

use App\Models\UserPaymentMethod;

class MediosCobro extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');
        $model = new UserPaymentMethod();
        $medios = $model->getAllByUser($userId);

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
            'tipo' => 'required|in_list[alias,cbu_cvu,link,otro]',
            'alias' => 'permit_empty|max_length[255]',
            'cbu_cvu' => 'permit_empty|max_length[255]',
            'banco' => 'permit_empty|max_length[255]',
            'titular' => 'permit_empty|max_length[255]',
            'payment_link' => 'permit_empty|max_length[500]|valid_url_strict',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alias = $this->request->getPost('alias');
        $cbuCvu = $this->request->getPost('cbu_cvu');
        $paymentLink = $this->request->getPost('payment_link');

        if (empty($alias) && empty($cbuCvu) && empty($paymentLink)) {
            return redirect()->back()->withInput()->with('errors', [
                'alias' => 'Debe proporcionar al menos uno: Alias, CBU/CVU o Link de pago.',
            ]);
        }

        $model = new UserPaymentMethod();
        $model->insert([
            'user_id' => session()->get('userId'),
            'tipo' => $this->request->getPost('tipo'),
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

        if (!$medio || (int) $medio['user_id'] !== session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio de cobro no encontrado.');
        }

        return view('mis-medios/form', ['medio' => $medio]);
    }

    public function update(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio de cobro no encontrado.');
        }

        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
            'tipo' => 'required|in_list[alias,cbu_cvu,link,otro]',
            'alias' => 'permit_empty|max_length[255]',
            'cbu_cvu' => 'permit_empty|max_length[255]',
            'banco' => 'permit_empty|max_length[255]',
            'titular' => 'permit_empty|max_length[255]',
            'payment_link' => 'permit_empty|max_length[500]|valid_url_strict',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alias = $this->request->getPost('alias');
        $cbuCvu = $this->request->getPost('cbu_cvu');
        $paymentLink = $this->request->getPost('payment_link');

        if (empty($alias) && empty($cbuCvu) && empty($paymentLink)) {
            return redirect()->back()->withInput()->with('errors', [
                'alias' => 'Debe proporcionar al menos uno: Alias, CBU/CVU o Link de pago.',
            ]);
        }

        $model->update($id, [
            'tipo' => $this->request->getPost('tipo'),
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

        if (!$medio || (int) $medio['user_id'] !== session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio de cobro no encontrado.');
        }

        $model->delete($id);

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Medio de cobro eliminado correctamente.');
    }

    public function toggle(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio de cobro no encontrado.');
        }

        $nuevoEstado = (int) $medio['activo'] ? 0 : 1;
        $model->update($id, ['activo' => $nuevoEstado]);

        $mensaje = $nuevoEstado
            ? 'Medio de cobro activado correctamente.'
            : 'Medio de cobro desactivado correctamente.';

        return redirect()->to('/mis-medios-de-cobro')->with('success', $mensaje);
    }

    public function favorito(int $id)
    {
        $model = new UserPaymentMethod();
        $medio = $model->find($id);

        if (!$medio || (int) $medio['user_id'] !== session()->get('userId')) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Medio de cobro no encontrado.');
        }

        if (!(int) $medio['activo']) {
            return redirect()->to('/mis-medios-de-cobro')->with('error', 'Solo se puede marcar como favorito un medio activo.');
        }

        $model->marcarFavorito($id, session()->get('userId'));

        return redirect()->to('/mis-medios-de-cobro')->with('success', 'Favorito actualizado correctamente.');
    }
}
