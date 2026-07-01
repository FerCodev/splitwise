<?php

return [
    'title' => 'Tarjeta de deuda pendiente',
    'description' => 'Muestra una deuda propia con monto, acreedor y acci&oacute;n para pagar.',
    'screen' => 'grupo_balance',
    'component' => 'deuda_pendiente_card',
    'selected' => $selectedDebtPendingVariant,
    'variants' => [
        [
            'key' => 'default',
            'name' => 'Por defecto',
            'hint' => 'Pill Le deb&eacute;s, monto destacado y CTA contextual.',
            'render' => static fn () => '<div style="display:grid;gap:12px">'
                . view('components/cards/deuda_pendiente', [
                    'monto' => 119539, 'acreedorNombre' => 'Fernando', 'acreedorId' => 2,
                    'grupoId' => 1, 'grupoEstado' => 'activo', 'mediosCobro' => [],
                    'formId' => 'cat-deuda-activo', 'fechaDefault' => '2026-07-15',
                ])
                . view('components/cards/deuda_pendiente', [
                    'monto' => 8500, 'acreedorNombre' => 'Juan', 'acreedorId' => 3,
                    'grupoId' => 1, 'grupoEstado' => 'cerrado', 'mediosCobro' => [
                        ['nombre' => 'Mercado Pago', 'alias' => 'juan.mp', 'cbu_cvu' => '', 'payment_link' => '', 'banco' => ''],
                    ],
                    'formId' => 'cat-deuda-cerrado', 'fechaDefault' => '2026-07-15',
                ])
                . view('components/cards/deuda_pendiente', [
                    'monto' => 15000, 'acreedorNombre' => 'Maria', 'acreedorId' => 4,
                    'grupoId' => 1, 'grupoEstado' => 'activo', 'mediosCobro' => [],
                    'formId' => 'cat-deuda-sin-medios', 'fechaDefault' => '2026-07-15',
                ])
                . view('components/cards/deuda_pendiente', [
                    'monto' => 3200, 'acreedorNombre' => 'Pedro', 'acreedorId' => 5,
                    'grupoId' => 1, 'grupoEstado' => 'liquidado', 'mediosCobro' => [],
                    'formId' => 'cat-deuda-liquidado', 'fechaDefault' => '2026-07-15',
                ])
                . '</div>',
        ],
    ],
];
