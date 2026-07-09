<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UxFeedbackComponentsTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        service('renderer')->resetData();
    }

    public function testDeleteFormDefaultConfirmMessageHasProperAccents(): void
    {
        $html = view('components/forms/delete_form', [
            'action' => '/demo/delete',
            'formId' => 'delete-demo',
        ]);

        $this->assertStringContainsString('data-confirm-msg="¿Estás seguro?"', html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
    }

    public function testDeleteFormCanSubmitPlainPostActions(): void
    {
        $html = view('components/forms/delete_form', [
            'action' => '/amigos/1/eliminar',
            'formId' => 'eliminar-amistad-1',
            'methodOverride' => false,
        ]);

        $this->assertStringNotContainsString('name="_method"', $html);
    }

    public function testConfirmModalSupportsFormsAndJsActions(): void
    {
        $html = view('partials/_confirm_modal');

        $this->assertStringContainsString('¿Estás seguro?', $html);
        $this->assertStringContainsString('data-confirm-action', $html);
        $this->assertStringContainsString('gastito:confirm-action', $html);
        $this->assertStringContainsString('confirmBtn.disabled = true', $html);
    }

    public function testErrorAlertsUseLongerDuration(): void
    {
        $html = view('partials/_alert', [
            'type' => 'error',
            'message' => 'No se pudo copiar al portapapeles.',
        ]);

        $this->assertStringContainsString('--feedback-duration: 12000ms', $html);
    }

    public function testFooterExposesGastitoFeedbackHelper(): void
    {
        $html = view('partials/_footer');

        $this->assertStringContainsString('window.GastitoFeedback', $html);
        $this->assertStringContainsString('gastito-feedback-live', $html);
        $this->assertStringContainsString('No se pudo completar', $html);
    }

    public function testFooterExposesSharedMoneyInputHelper(): void
    {
        $html = view('partials/_footer');

        $this->assertStringContainsString('window.SplitWiseMoney', $html);
        $this->assertStringContainsString('formatTyping: formatTypingValue', $html);
        $this->assertStringContainsString('syncHidden: syncHidden', $html);
        $this->assertStringContainsString('[data-money-fill]', $html);
        $this->assertStringContainsString('data-money-max-message', $html);
    }

    public function testConfirmModalHasMobileSpacingStyles(): void
    {
        $css = file_get_contents(ROOTPATH . 'public/assets/app.css');

        $this->assertStringContainsString('#confirmModal .modal-dialog', $css);
        $this->assertStringContainsString('width: calc(100% - 48px)', $css);
        $this->assertStringContainsString('#confirmModal .modal-footer', $css);
    }

    public function testReceiptDeleteUsesAjaxFeedbackInsteadOfReloadOnly(): void
    {
        $view = file_get_contents(APPPATH . 'Views/gastos/form.php');

        $this->assertStringContainsString('data-confirm-action="delete-receipt"', $view);
        $this->assertStringContainsString("'Accept': 'application/json'", $view);
        $this->assertStringContainsString("GastitoFeedback.show('success'", $view);
        $this->assertStringContainsString("reciboActual.remove()", $view);
    }

    public function testMobileBalanceKeepsReadableTypography(): void
    {
        $css = file_get_contents(ROOTPATH . 'public/assets/app.css');

        $this->assertStringContainsString('.balance-overview-stats strong', $css);
        $this->assertStringContainsString('font-size: 15px', $css);
        $this->assertStringContainsString('.balance-member-result strong', $css);
        $this->assertStringContainsString('font-size: 22px', $css);
        $this->assertStringContainsString('.balance-member-breakdown > div', $css);
    }
}
