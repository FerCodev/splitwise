<?php

return ['key' => 'session_expiring', 'name' => 'Sesion por vencer', 'hint' => 'Aviso de seguridad.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-session"><span class="tabler-alert-icon">!</span><div><strong>Tu sesi&oacute;n est&aacute; por vencer</strong><small>Guard&aacute; los cambios antes de continuar.</small></div><button class="btn btn-outline-secondary btn-sm" type="button">Seguir</button></div>'];
