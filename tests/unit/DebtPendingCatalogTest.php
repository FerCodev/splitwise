<?php

use CodeIgniter\Test\CIUnitTestCase;

final class DebtPendingCatalogTest extends CIUnitTestCase
{
    public function testInventarioIncluyeTarjetaDeDeudaPendiente(): void
    {
        $selectedDebtVariant = 'soft';
        $selectedHomeGroupVariant = 'minimal_panel';
        $selectedExpensesTotalVariant = 'simple';
        $selectedPaymentsTotalVariant = 'simple';
        $selectedPaymentMethodVariant = 'bank_card';
        $selectedGroupBalanceVariant = 'status_pill';
        $selectedGaugeVariant = 'semicircle';
        $selectedMovementVariant = 'feed';
        $selectedDebtPendingVariant = 'default';
        $selectedAlertSuccessVariant = 'success_compact';
        $selectedAlertErrorVariant = 'error_action';
        $selectedAlertWarningVariant = 'warning_debt';
        $selectedAlertInfoVariant = 'info_filter';
        $selectedAlertDestructiveConfirmationVariant = 'delete_confirmation';
        $selectedAlertEmptyStateVariant = 'empty_group';
        $selectedAlertSecuritySessionVariant = 'admin_permission';
        $selectedAlertProcessExportVariant = 'export_ready';
        $selectedAlertPaymentSuggestionVariant = 'suggested_payment';
        $selectedAlertGroupEventVariant = 'balance_recalculated';
        $catalogDemoData = [
            'fechaDemo' => '2026-07-15',
            'fechaCorta' => '15/07/2026',
        ];

        $catalog = require APPPATH . 'Catalog/componentes.php';
        $balanceComponents = $catalog['balance'] ?? [];
        $debtComponent = array_values(array_filter(
            $balanceComponents,
            static fn (array $component): bool => ($component['component'] ?? null) === 'deuda_pendiente_card'
        ));

        $this->assertCount(1, $debtComponent);
        $this->assertSame('grupo_balance', $debtComponent[0]['screen']);
        $this->assertSame('default', $debtComponent[0]['selected']);
        $this->assertSame(['default'], array_column($debtComponent[0]['variants'], 'key'));
    }
}
