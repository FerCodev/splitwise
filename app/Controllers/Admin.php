<?php

namespace App\Controllers;

use App\Models\CatalogDesignCuration;
use App\Services\UiComponentResolver;
use Throwable;

class Admin extends BaseController
{
    public function catalogoTarjetas(?string $pantalla = null)
    {
        try {
            $catalogCurationState = model(CatalogDesignCuration::class)->allByDesignId();
        } catch (Throwable) {
            $catalogCurationState = [];
        }

        return view('admin/catalogo_tarjetas', [
            'activeScreen' => (string) $pantalla,
            'catalogCurationState' => $catalogCurationState,
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

    public function guardarCuraduria()
    {
        $designId = trim((string) $this->request->getPost('design_id'));
        $designName = trim((string) $this->request->getPost('design_name'));
        $designGroup = trim((string) $this->request->getPost('design_group'));
        $status = trim((string) $this->request->getPost('status'));
        $redesignNote = trim((string) $this->request->getPost('redesign_note'));
        $inUse = (string) $this->request->getPost('in_use') === '1';

        if ($designId === '' || $designName === '' || $designGroup === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Faltan datos del dise&ntilde;o.',
                'csrf' => csrf_hash(),
            ]);
        }

        if ($inUse && $status === CatalogDesignCuration::STATUS_DISCARDED) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Los dise&ntilde;os activos no se pueden descartar.',
                'csrf' => csrf_hash(),
            ]);
        }

        try {
            model(CatalogDesignCuration::class)->saveState($designId, $designName, $designGroup, $status, $redesignNote);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'No se pudo guardar la marca. Ejecut&aacute; las migraciones pendientes.',
                'csrf' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'csrf' => csrf_hash(),
        ]);
    }

    public function limpiarCuraduria()
    {
        try {
            model(CatalogDesignCuration::class)->clearAll();
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'No se pudieron limpiar las marcas. Ejecut&aacute; las migraciones pendientes.',
                'csrf' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'csrf' => csrf_hash(),
        ]);
    }
}
