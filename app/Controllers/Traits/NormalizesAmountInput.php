<?php

namespace App\Controllers\Traits;

trait NormalizesAmountInput
{
    private function normalizarMonto(): void
    {
        $raw = $this->request->getPost('monto');
        $visual = $this->request->getPost('monto_visual');

        $candidato = $raw;
        $deVisual = false;
        if ($candidato === null || $candidato === '') {
            $candidato = $visual;
            $deVisual = true;
        }
        if ($candidato === null || $candidato === '') {
            return;
        }

        $limpio = $candidato;

        if (str_contains($limpio, ',')) {
            $limpio = str_replace('.', '', $limpio);
            $limpio = str_replace(',', '.', $limpio);
        } elseif ($deVisual) {
            $limpio = str_replace('.', '', $limpio);
        }

        if (is_numeric($limpio)) {
            $this->request->setGlobal('post', array_merge($this->request->getPost() ?: [], ['monto' => $limpio]));
        }
    }
}
