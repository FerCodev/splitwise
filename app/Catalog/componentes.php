<?php

return [
    'home' => [
        require __DIR__ . '/Components/home/home_group_card.php',
        require __DIR__ . '/Components/home/debt_card.php',
    ],
    'grupos' => [
        require __DIR__ . '/Components/grupos/group_movement_card.php',
        require __DIR__ . '/Components/grupos/group_gauge.php',
    ],
    'gastos' => [
        require __DIR__ . '/Components/gastos/filtered_total_card.php',
    ],
    'pagos' => [
        require __DIR__ . '/Components/pagos/filtered_total_card.php',
    ],
    'medios' => [
        require __DIR__ . '/Components/medios/payment_method_card.php',
    ],
];