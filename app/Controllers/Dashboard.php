<?php

namespace App\Controllers;

use App\Models\Grupo;

class Dashboard extends BaseController
{
    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        return view('dashboard', [
            'grupos' => $grupos,
        ]);
    }
}
