<?php

return ['key' => 'suggested_payment', 'name' => 'Pago sugerido', 'hint' => 'Alerta con monto protagonista.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-payment"><div><small>Pago sugerido</small><strong>Antonella &rarr; Fernando</strong></div><b>' . moneda(116351) . '</b></div>'];
