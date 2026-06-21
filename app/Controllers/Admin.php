<?php

namespace App\Controllers;

use App\Services\UiComponentResolver;
use Throwable;

class Admin extends BaseController
{
    public function catalogoTarjetas(?string $pantalla = null)
    {
        return view('admin/catalogo_tarjetas', [
            'activeScreen' => (string) $pantalla,
            'selectedDebtVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_HOME,
                UiComponentResolver::COMPONENT_DEBT_CARD
            ),
            'selectedHomeGroupVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_HOME,
                UiComponentResolver::COMPONENT_HOME_GROUP_CARD
            ),
            'selectedExpensesTotalVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_EXPENSES_INDEX,
                UiComponentResolver::COMPONENT_FILTERED_TOTAL_CARD
            ),
            'selectedPaymentsTotalVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_PAYMENTS_INDEX,
                UiComponentResolver::COMPONENT_FILTERED_TOTAL_CARD
            ),
            'selectedPaymentMethodVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_PAYMENT_METHODS,
                UiComponentResolver::COMPONENT_PAYMENT_METHOD_CARD
            ),
            'selectedGaugeVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_GROUP_SHOW,
                UiComponentResolver::COMPONENT_GROUP_GAUGE
            ),
            'selectedMovementVariant' => UiComponentResolver::variant(
                UiComponentResolver::SCREEN_GROUP_SHOW,
                UiComponentResolver::COMPONENT_GROUP_MOVEMENT_CARD
            ),
        ]);
    }

    public function guardarComponente()
    {
        $screenKey = (string) $this->request->getPost('screen_key');
        $componentKey = (string) $this->request->getPost('component_key');
        $variantKey = (string) $this->request->getPost('variant_key');
        $returnScreen = (string) $this->request->getPost('return_screen');
        $returnUrl = base_url('admin/catalogo-tarjetas' . ($returnScreen ? '/' . rawurlencode($returnScreen) : ''));

        try {
            $saved = UiComponentResolver::setVariant($screenKey, $componentKey, $variantKey);
        } catch (Throwable) {
            return redirect()->to($returnUrl)->with('error', 'No se pudo guardar la preferencia. Ejecut&aacute; las migraciones pendientes.');
        }

        if (!$saved) {
            return redirect()->to($returnUrl)->with('error', 'La variante seleccionada no es v&aacute;lida.');
        }

        return redirect()->to($returnUrl)->with('success', 'Componente actualizado correctamente.');
    }
}

