# Roadmap Grupo y Actividad - Ajustes UX

Actualizado: 2026-06-16.

Este documento registra pedidos visuales pendientes para una proxima iteracion.
No ejecutar todavia sin confirmacion. No reemplaza `roadmap-visuales.md`; lo
complementa con ajustes detectados despues de UX4 y la reorganizacion de grupo.

## Objetivo

Hacer que la pantalla del grupo se sienta consistente con la actividad reciente
de Home: movimientos como cards independientes, clickeables, legibles y menos
encerrados en un panel administrativo.

## Pedidos registrados

### 1. Quitar fecha de creacion del grupo

Pantalla:

- `app/Views/grupos/show.php`

Cambio pedido:

- Sacar el texto:
  - `Creado el dd/mm/yyyy hh:mm`

Motivo:

- No aporta al uso diario.
- Ensucia el header del grupo.

Aceptacion:

- La card principal del grupo muestra nombre, estado, saldo/rol y boton
  `Configurar grupo`, pero no muestra fecha de creacion.

### 2. Movimientos dentro del grupo como cards independientes

Pantalla:

- `app/Views/grupos/show.php`

Estado actual:

- Los movimientos estan dentro de una card grande con header `Movimientos` y
  botones `Gastos` / `Pagos`.
- Dentro hay filas/list items.

Cambio pedido:

- Los movimientos del grupo deben verse como cards independientes, igual que
  `Actividad reciente` en Home.
- No deben sentirse como tabla o lista encerrada en un bloque grande.
- Cada movimiento debe tener su propia card con:
  - descripcion;
  - grupo o contexto si aplica;
  - monto alineado a la derecha;
  - fecha;
  - persona;
  - categoria/badge si existe.

Referencia visual:

- Usar como base la seccion `Actividad reciente` de `app/Views/dashboard.php`.

Aceptacion:

- En mobile, cada movimiento se ve como card separada con sombra/borde suave.
- No hay un borde grande encerrando todos los movimientos.
- El layout es consistente con Home.

### 3. Movimientos del grupo clickeables

Pantalla:

- `app/Views/grupos/show.php`

Cambio pedido:

- Al tocar/clickear un movimiento del grupo, abrir su detalle.

Rutas esperadas:

- Si es gasto:
  - `/gastos/{id}`
- Si es pago:
  - `/pagos/{id}`

Aceptacion:

- Cada card de movimiento tiene enlace real al detalle.
- El enlace ocupa toda la card o al menos una zona clara tocable.
- No se rompe el click de botones secundarios si se conservan.

### 4. Revisar acciones de movimientos

Pantalla:

- `app/Views/grupos/show.php`

Pendiente de decidir:

- Si los botones `Gastos` y `Pagos` deben seguir en la seccion o moverse a otro
  lugar.

Preferencia inicial:

- Mantener acceso a ver todos los gastos/pagos, pero sin que opaque la lista de
  movimientos recientes.
- Podria ser una fila de acciones secundaria debajo del titulo o al final de la
  seccion.

## Fuera de alcance

- No cambiar calculo de balance.
- No cambiar permisos.
- No cambiar rutas.
- No cambiar alta/edicion de gastos.
- No tocar limpieza Ponytail.
- No tocar reportes.

## Verificaciones esperadas cuando se implemente

```bash
php -l app/Views/grupos/show.php
php vendor/bin/phpunit --no-coverage
git diff --check
```

Si se toca CSS:

```bash
php -l app/Views/grupos/show.php
```

QA visual recomendado:

- Mobile ancho aproximado 360px.
- Grupo con 0 movimientos.
- Grupo con 1 movimiento.
- Grupo con varios movimientos.
- Click en gasto abre detalle de gasto.
- Click en pago abre detalle de pago.

## Criterios de cierre

- Header del grupo sin fecha de creacion.
- Movimientos se ven como cards independientes, consistentes con Home.
- Cards de movimientos abren detalle.
- No hay regresiones en la pantalla principal de Home.
- Tests siguen pasando.

