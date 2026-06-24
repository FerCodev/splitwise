<?php

return ['key' => 'synced', 'name' => 'Sincronizado', 'hint' => 'Estado remoto actualizado.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-sync"><span class="tabler-alert-icon">&check;</span><div><strong>Todo sincronizado</strong><small>Los cambios locales ya est&aacute;n en GitHub.</small></div><span class="badge bg-success">Push</span></div>'];
