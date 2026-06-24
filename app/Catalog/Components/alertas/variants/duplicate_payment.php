<?php

return ['key' => 'duplicate_payment', 'name' => 'Pago duplicado', 'hint' => 'Prevenci&oacute;n de carga repetida.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-duplicate"><span class="tabler-alert-icon">2</span><div><strong>Posible pago duplicado</strong><small>Ya existe un pago similar cargado hoy por ' . moneda(30000) . '.</small></div></div>'];
