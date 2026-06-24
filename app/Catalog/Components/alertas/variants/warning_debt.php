<?php

return ['key' => 'warning_debt', 'name' => 'Warning deuda', 'hint' => 'Deuda pendiente sin sonar agresiva.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-warning"><span class="tabler-alert-icon">$</span><div><strong>Ten&eacute;s una deuda pendiente</strong><small>Le deb&eacute;s a Fernando ' . moneda(112697) . ' del grupo Junio.</small></div></div>'];
