<?php

use CodeIgniter\Test\CIUnitTestCase;

final class GrupoBalanceCardTest extends CIUnitTestCase
{
    private function renderCard(array $overrides = []): string
    {
        $data = array_merge([
            'saldo' => 0,
            'href' => '/grupos/1/balance',
        ], $overrides);

        return view('components/cards/grupo_balance', $data);
    }

    public function testCardSaldoPositivoRenderizaComoEnlace(): void
    {
        $html = $this->renderCard(['saldo' => 50000]);

        $this->assertStringContainsString('<a ', $html);
        $this->assertStringContainsString('href="', $html);
        $this->assertStringContainsString('balance"', $html);
        $this->assertStringContainsString('Te deben', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('data-bs-toggle', $html);
        $this->assertStringNotContainsString('data-bs-target', $html);
    }

    public function testCardSaldoNegativoRenderizaComoEnlace(): void
    {
        $html = $this->renderCard(['saldo' => -30000]);

        $this->assertStringContainsString('<a ', $html);
        $this->assertStringContainsString('href="', $html);
        $this->assertStringContainsString('balance"', $html);
        $this->assertStringContainsString('Debés', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('data-bs-toggle', $html);
        $this->assertStringNotContainsString('data-bs-target', $html);
    }

    public function testCardSaldoCeroRenderizaComoEnlace(): void
    {
        $html = $this->renderCard(['saldo' => 0]);

        $this->assertStringContainsString('<a ', $html);
        $this->assertStringContainsString('href="', $html);
        $this->assertStringContainsString('balance"', $html);
        $this->assertStringContainsString('Saldado', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('data-bs-toggle', $html);
        $this->assertStringNotContainsString('data-bs-target', $html);
    }

    public function testCardNoTieneModalTarget(): void
    {
        $casos = [50000, -30000, 0, 1.50, -0.01];

        foreach ($casos as $saldo) {
            $html = $this->renderCard(['saldo' => $saldo]);
            $this->assertStringNotContainsString('data-bs-toggle="modal"', $html,
                "Saldo $saldo no deberia tener data-bs-toggle=modal"
            );
            $this->assertStringNotContainsString('data-bs-target', $html,
                "Saldo $saldo no deberia tener data-bs-target"
            );
        }
    }

    public function testCardMuestraMontoFormateado(): void
    {
        $html = $this->renderCard(['saldo' => 164865]);

        $this->assertStringContainsString('Tu balance', $html);
        $this->assertStringContainsString('$164.865,00', $html);
    }

    public function testCardMontoNegativoSinSigno(): void
    {
        $html = $this->renderCard(['saldo' => -5000]);

        $this->assertStringContainsString('Debés', $html);
        $this->assertStringNotContainsString('$-', $html);
    }

    public function testCardClasePositivaParaSaldoAFavor(): void
    {
        $html = $this->renderCard(['saldo' => 100]);

        $this->assertStringContainsString('is-positive', $html);
        $this->assertStringContainsString('text-success', $html);
    }

    public function testCardClaseNegativaParaDeuda(): void
    {
        $html = $this->renderCard(['saldo' => -100]);

        $this->assertStringContainsString('is-negative', $html);
        $this->assertStringContainsString('text-danger', $html);
    }

    public function testCardClaseNeutraParaSaldado(): void
    {
        $html = $this->renderCard(['saldo' => 0]);

        $this->assertStringContainsString('is-settled', $html);
        $this->assertStringContainsString('text-muted', $html);
    }
}
