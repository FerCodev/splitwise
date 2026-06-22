<?php

namespace App\Controllers;

use App\Models\UiComponentCatalogDecision;
use App\Services\UiComponentResolver;
use Throwable;

class Admin extends BaseController
{
    public function catalogoTarjetas(?string $pantalla = null)
    {
        $componentDecisions = [];

        try {
            $componentDecisions = model(UiComponentCatalogDecision::class)->decisionMap();
        } catch (Throwable) {
            $componentDecisions = [];
        }

        return view('admin/catalogo_tarjetas', [
            'activeScreen' => (string) $pantalla,
            'componentDecisions' => $componentDecisions,
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

    public function guardarDecisionCatalogo()
    {
        $decision = (string) $this->request->getPost('decision');
        $returnUrl = (string) $this->request->getPost('return_url');

        if (!in_array($decision, UiComponentCatalogDecision::allowedDecisions(), true)) {
            return redirect()->to($this->catalogReturnUrl($returnUrl))->with('error', 'La acci&oacute;n seleccionada no es v&aacute;lida.');
        }

        $notes = trim((string) $this->request->getPost('redesign_notes'));
        if ($decision === UiComponentCatalogDecision::DECISION_REDESIGN && $notes === '') {
            return redirect()->to($this->catalogReturnUrl($returnUrl))->with('error', 'Agreg&aacute; indicaciones para redise&ntilde;ar el componente.');
        }

        try {
            model(UiComponentCatalogDecision::class)->setDecision([
                'catalog_key' => (string) $this->request->getPost('catalog_key'),
                'section_key' => (string) $this->request->getPost('section_key'),
                'group_key' => (string) $this->request->getPost('group_key'),
                'item_key' => (string) $this->request->getPost('item_key'),
                'item_name' => (string) $this->request->getPost('item_name'),
                'item_hint' => (string) $this->request->getPost('item_hint') ?: null,
                'source_label' => (string) $this->request->getPost('source_label') ?: null,
                'decision' => $decision,
                'redesign_notes' => $notes !== '' ? $notes : null,
                'created_by' => session()->get('userId') ?: null,
            ]);
        } catch (Throwable) {
            return redirect()->to($this->catalogReturnUrl($returnUrl))->with('error', 'No se pudo guardar la marca. Ejecut&aacute; las migraciones pendientes.');
        }

        $message = match ($decision) {
            UiComponentCatalogDecision::DECISION_IMPLEMENT => 'Componente marcado para implementar.',
            UiComponentCatalogDecision::DECISION_DISCARD => 'Componente descartado del cat&aacute;logo.',
            UiComponentCatalogDecision::DECISION_REDESIGN => 'Componente marcado para redise&ntilde;ar.',
        };

        return redirect()->to($this->catalogReturnUrl($returnUrl))->with('success', $message);
    }

    private function catalogReturnUrl(string $returnUrl): string
    {
        $base = base_url('admin/catalogo-tarjetas');

        if (str_starts_with($returnUrl, $base)) {
            return $returnUrl;
        }

        return $base;
    }
}
