<?php

namespace App\Controllers;

use App\Services\UiComponentResolver;
use Throwable;

class Admin extends BaseController
{
    public function catalogoTarjetas()
    {
        return view('admin/catalogo_tarjetas', [
            'selectedDebtVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_HOME,
                UiComponentResolver::COMPONENT_DEBT_CARD
            ),
            'selectedGaugeVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_GROUP_SHOW,
                UiComponentResolver::COMPONENT_GROUP_GAUGE
            ),
        ]);
    }

    public function guardarComponente()
    {
        $screenKey = (string) $this->request->getPost('screen_key');
        $componentKey = (string) $this->request->getPost('component_key');
        $variantKey = (string) $this->request->getPost('variant_key');

        try {
            $saved = UiComponentResolver::setVariant($screenKey, $componentKey, $variantKey);
        } catch (Throwable) {
            return redirect()->back()->with('error', 'No se pudo guardar la preferencia. Ejecut&aacute; las migraciones pendientes.');
        }

        if (!$saved) {
            return redirect()->back()->with('error', 'La variante seleccionada no es v&aacute;lida.');
        }

        return redirect()->back()->with('success', 'Componente actualizado correctamente.');
    }
}
