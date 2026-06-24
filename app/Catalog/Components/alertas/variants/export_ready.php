<?php

return ['key' => 'export_ready', 'name' => 'Exportacion lista', 'hint' => 'Feedback para PDF o Excel.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-export"><span class="tabler-alert-icon">&darr;</span><div><strong>Archivo preparado</strong><small>El reporte incluye total filtrado y 41 movimientos.</small></div><button class="btn btn-primary btn-sm" type="button">Descargar</button></div>'];
