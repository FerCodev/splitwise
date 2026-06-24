# Roadmap activo - SplitWise

Este es el unico roadmap que debe usar el agente para decidir la proxima fase.

Los archivos guardados en `Roadmaps historico/` son material de referencia y no deben ejecutarse salvo que se indique explicitamente.

## Estado actual

- La app principal esta estable sobre `main`.
- Produccion/deploy queda diferido.
- Periodos queda descartado.
- Los roadmaps historicos no son fuente activa de trabajo.

## Fases completadas

### Fase A - Pantalla de grupo: actividad clara y menos ruido

Completada en `feature/grupo-actividad-cards`, mergeada como PR #20, commit `1bec3db`.

Cambios:
- Fecha de creacion eliminada del header del grupo.
- Movimientos del grupo convertidos a cards independientes con estilo dashboard.
- Cada movimiento es clickeable a `/gastos/{id}` o `/pagos/{id}`.
- Botones Gastos/Pagos movidos a linea del titulo, mas compactos.
- Muestra hasta 10 movimientos recientes.
- Categoria inferida visible como badge.
- Sin cambios en backend, rutas, migraciones, permisos ni balance.

## Proxima fase recomendada

## Pendientes tecnicos no bloqueantes

### Fase B - Cierre tecnico UX4 de division de gastos

Completada en `feature/fase-b-documentacion-ux4`, mergeada como PR #21, commit `c21b1d8`.

Logros:
- Documentado en Gasto.php: getBalanceByGrupo() indica que gasto_divisiones.monto_calculado es fuente primaria y gasto_participantes.monto_asignado es fallback legacy.
- Documentado en GastoDivision.php: partes/ajuste no estan expuestos.
- Sin cambios de comportamiento funcional.

### Fase C - Limpieza de codigo muerto

Completada en `feature/fase-c-limpieza-codigo-muerto`, mergeada como PR #22, commit `0c12602`.

Eliminado:
- `_division_modal.php` (177 lineas)
- `Home.php` (11 lineas)
- `Gasto::getSaldosByGrupo()` (48 lineas)
- `GastoDivision::getGastosSinDivisiones()` y metodos relacion (22 lineas)
- `UserPaymentMethod::getAllByUser()` y `getFavoritoByUser()` (18 lineas)
- Total: -276 lineas, sin tests afectados.

La limpieza segura de codigo muerto esta completa. No hay mas items de este tipo pendientes sin revisar.

## Proxima fase recomendada

No hay fases de limpieza tecnica pendientes. El proximo foco debe ser una fase de producto/UX.

Posibles candidatos:
- Mejoras en dashboard (metricas, filtros, ordenamiento).
- UX de pagos (asistencia, recordatorios).
- Refinamiento de reportes.
- Lo que decidas como prioridad de producto.

## Diferido explicitamente

- Produccion/deploy.
- Periodos.
- Redisenios grandes de navegacion.
- Nuevos modos avanzados de division no definidos.
- Registro publico.

