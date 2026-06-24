<?php

return [
    'title' => 'Veloc&iacute;metro de aporte',
    'description' => 'Widget que compara lo pagado por el usuario contra el total gastado del grupo.',
    'screen' => 'grupo_show',
    'component' => 'group_gauge',
    'selected' => $selectedGaugeVariant,
    'variants' => [
        ['key' => 'semicircle', 'name' => 'Actual', 'hint' => 'Aguja semicircular.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'semicircle', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
        ['key' => 'compact_dial', 'name' => 'Dial compacto', 'hint' => 'Score central y montos.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'compact_dial', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
        ['key' => 'scale_bar', 'name' => 'Barra de escala', 'hint' => 'Lectura horizontal.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'scale_bar', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
        ['key' => 'segmented_donut', 'name' => 'Dona segmentada', 'hint' => 'Dial circular con zonas.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'segmented_donut', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
    ],
];