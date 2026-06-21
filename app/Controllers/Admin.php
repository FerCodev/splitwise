<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function catalogoTarjetas()
    {
        return view('admin/catalogo_tarjetas');
    }
}
