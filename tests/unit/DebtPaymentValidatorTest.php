<?php

use App\Services\DebtPaymentValidator;
use CodeIgniter\Test\CIUnitTestCase;

final class DebtPaymentValidatorTest extends CIUnitTestCase
{
    public function testConvierteMontosDecimalesAcentavos(): void
    {
        $this->assertSame(12345, DebtPaymentValidator::amountToCents('123.45'));
        $this->assertSame(12345, DebtPaymentValidator::amountToCents('123,45'));
        $this->assertSame(100, DebtPaymentValidator::amountToCents('1'));
        $this->assertSame(1, DebtPaymentValidator::amountToCents('0.01'));
    }

    public function testRechazaMontosInvalidos(): void
    {
        $this->assertNull(DebtPaymentValidator::amountToCents(null));
        $this->assertNull(DebtPaymentValidator::amountToCents('0'));
        $this->assertNull(DebtPaymentValidator::amountToCents('-1'));
        $this->assertNull(DebtPaymentValidator::amountToCents('1.234'));
        $this->assertNull(DebtPaymentValidator::amountToCents('abc'));
    }

    public function testValidaPagoParcialYTotal(): void
    {
        $debt = ['monto' => '100.00'];

        $this->assertNull(DebtPaymentValidator::validateCurrentDebt($debt, '40.25'));
        $this->assertNull(DebtPaymentValidator::validateCurrentDebt($debt, '100.00'));
    }

    public function testRechazaSobrepagoYDeudaInexistente(): void
    {
        $this->assertSame(
            'El monto no puede superar la deuda pendiente.',
            DebtPaymentValidator::validateCurrentDebt(['monto' => '100.00'], '100.01')
        );
        $this->assertStringContainsString('saldada o cambió', DebtPaymentValidator::validateCurrentDebt(null, '10.00'));
    }

    public function testRechazaMontoNoPositivoContraDeuda(): void
    {
        $error = DebtPaymentValidator::validateCurrentDebt(['monto' => '100.00'], '0');

        $this->assertStringContainsString('mayor a cero', $error);
    }
}
