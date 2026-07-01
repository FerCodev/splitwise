<?php

return [
    'title' => 'Balance personal del grupo',
    'description' => 'Resume si al usuario le deben, debe o est&aacute; saldado dentro del grupo.',
    'screen' => 'grupo_show',
    'component' => 'group_balance_card',
    'selected' => $selectedGroupBalanceVariant,
    'variants' => [
        [
            'key' => 'status_pill',
            'name' => 'Estado con pill',
            'hint' => 'Siempre enlaza a la pantalla de balance. Muestra Te deben, Deb&eacute;s o Saldado seg&uacute;n el saldo.',
            'render' => static fn () => view('components/cards/grupo_balance', [
                'variant' => 'status_pill',
                'saldo' => 164865,
                'href' => '#',
            ]),
        ],
    ],
];
