<?php

return ['key' => 'settlement_recommendation', 'name' => 'Liquidacion recomendada', 'hint' => 'Sugerencia inteligente.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-recommend"><span class="tabler-alert-icon">$</span><div><strong>Conviene liquidar</strong><small>Un pago de ' . moneda(14570) . ' deja el grupo saldado.</small></div></div>'];
