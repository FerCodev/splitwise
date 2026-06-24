<?php

return ['key' => 'incomplete_payment_method', 'name' => 'Medio incompleto', 'hint' => 'Dato faltante en medio de cobro.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-warning"><span class="tabler-alert-icon">!</span><div><strong>Falta alias o CBU</strong><small>Complet&aacute; al menos un dato para compartir este medio.</small></div></div>'];
