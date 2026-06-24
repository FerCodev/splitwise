<?php

return [
    'title' => 'Tarjeta de medio de cobro',
    'description' => 'Define c&oacute;mo se muestra cada medio de cobro guardado.',
    'screen' => 'mis_medios_cobro',
    'component' => 'payment_method_card',
    'selected' => $selectedPaymentMethodVariant,
    'variants' => [
        ['key' => 'bank_card', 'name' => 'Credencial actual', 'hint' => 'Azul, favorita y configuraci&oacute;n.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'bank_card', 'nombre' => 'Banco Galicia', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fernando.montesdeoca'])],
        ['key' => 'compact', 'name' => 'Compacta operativa', 'hint' => 'Listado eficiente.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'compact', 'nombre' => 'CBU Santander', 'activo' => true, 'favorito' => false, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fer.santander'])],
        ['key' => 'favorite', 'name' => 'Favorita destacada', 'hint' => 'Medio principal.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'favorite', 'nombre' => 'Mercado Pago', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando', 'alias' => 'fer.mp'])],
    ],
];