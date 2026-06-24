<?php

return ['key' => 'settled', 'name' => 'Liquidado', 'hint' => 'Estado final positivo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-settled"><span class="tabler-alert-icon">&check;</span><div><strong>Grupo liquidado</strong><small>No quedan pagos ni cobros pendientes.</small></div><span class="badge bg-success">OK</span></div>'];
