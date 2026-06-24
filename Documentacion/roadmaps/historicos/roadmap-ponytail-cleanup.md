# Roadmap Ponytail Cleanup - SplitWise

Actualizado: 2026-06-16.

Este documento clasifica la auditoria `ponytail-audit` para evitar borrar codigo
sin validar impacto de dominio, tests o roadmap. No reemplaza
`roadmap-ux4-remediacion.md`: primero se estabiliza UX4, despues se limpia.

## Decision general

Orden recomendado:

1. Cerrar `roadmap-ux4-remediacion.md`.
2. Ejecutar tests completos.
3. Hacer una rama/commit separado para limpieza Ponytail.
4. Borrar solo codigo muerto confirmado.
5. Dejar refactors laterales para otra ronda.

Regla de seguridad:

- Si algo toca balance, pagos, permisos, password reset, reportes o tests, no se
  borra en automatico.
- Si un item solo esta usado por tests, decidir si el test documenta una regla
  real o si solo protege codigo historico.
- No mezclar limpieza con fixes contables.

## A - Borrar con bajo riesgo

Estos items no tienen usos detectados en `app/` ni `tests/`, o son scaffolding
sin ruta.

- `app/Views/partials/_division_modal.php`
  - Motivo: UX4 inlino la division en `gastos/form.php`.
  - Accion: borrar archivo.

- `app/Controllers/Home.php`
  - Motivo: no hay ruta a `Home::index`; `/` apunta a `Auth::login`.
  - Accion: borrar controller.

- `app/Models/Gasto.php::getSaldosByGrupo()`
  - Motivo: no tiene usos; `getBalanceByGrupo()` es el flujo vigente.
  - Accion: borrar metodo.

- `app/Models/GastoDivision.php::getGastosSinDivisiones()`
  - Motivo: helper de migracion no usado.
  - Accion: borrar metodo.

- `app/Models/GastoDivision.php::gasto()` y `usuario()`
  - Motivo: relaciones no usadas.
  - Accion: borrar metodos.

- `app/Models/User.php::getRole()`
  - Motivo: no tiene usos detectados.
  - Accion: borrar metodo si no se planea usarlo en controllers.

- `app/Models/UserPaymentMethod.php::getAllByUser()` y `getFavoritoByUser()`
  - Motivo: no tienen usos detectados; el flujo real usa `getActivosByUser()` y
    `marcarFavorito()`.
  - Accion: borrar si no se quiere mantenerlos como API publica del modelo.

## B - Borrar despues de UX4

Estos items parecen muertos, pero conviene tocarlos junto con la centralizacion
del calculo de division.

- `app/Models/GastoDivision.php::generarDivisionesIgualitarias()`
  - Motivo: no se llama desde controllers.
  - Riesgo: contiene logica de division que puede confundirse con fuente vigente.
  - Accion recomendada: primero extraer calculo unico para `Gastos::create()` y
    `Gastos::update()`. Despues borrar este metodo viejo.

- Branches `partes` y `ajuste` dentro de `generarDivisionesIgualitarias()`
  - Motivo: fuera de alcance UX4.
  - Accion: desaparecen si se borra el metodo completo.

## C - No borrar ahora

Estos items tienen tests o valor como contrato/documentacion de reglas. Pueden
limpiarse mas adelante, pero no en la misma rama que UX4.

- `app/Models/User.php::isAdmin()` y `hasRole()`
  - Usos: `tests/unit/RolesGlobalesTest.php`.
  - Decision: mantener por ahora o abrir una limpieza dedicada de roles/tests.

- `app/Models/PasswordReset.php::generarTokenPlano()`, `hashearToken()`,
  `estaExpirado()`
  - Usos: `tests/unit/PasswordResetTest.php`.
  - Decision: mantener. Son helpers puros testeables y documentan seguridad.

- `app/Services/Reportes.php::aplicarFiltros()`,
  `agruparGastosPorCategoria()`, `agruparGastosPorGrupo()`
  - Usos: `tests/unit/ReportesTest.php`.
  - Decision: mantener hasta decidir si esos tests siguen siendo necesarios.

- `app/Models/Grupo.php::getPermisos()`
  - Usos: `tests/unit/PermisosGrupoTest.php`.
  - Decision: si se elimina, actualizar tests a `GroupPermission::getAll()`.
    No mezclar con UX4.

## D - Refactors opcionales, no urgentes

- Fusionar `GroupPermission` dentro de `Grupo`
  - Decision: no recomendado por ahora.
  - Motivo: `GroupPermission` esta usado por `Gastos`, `Pagos`, `Grupos` y tests;
    como servicio mantiene permisos fuera del modelo.

- Deduplicar `verificarAccesoGrupo()` en `Gastos` y `Pagos`
  - Decision: razonable, pero baja prioridad.
  - Accion futura: mover a `BaseController` o trait si aparece un tercer uso.

- Reemplazar `formatearMonto()` con `Intl.NumberFormat`
  - Decision: micro-refactor opcional.
  - No hacerlo mientras UX4 este en estabilizacion.

- Reemplazar `strcmp()` por `strtotime()` en ordenamientos de fecha
  - Decision: no necesario si las fechas estan en formato SQL/ISO.
  - `strcmp()` es simple y correcto para fechas `YYYY-MM-DD`.

## Plan de ejecucion sugerido

### Fase 1 - UX4 Remediacion

Ejecutar `roadmap-ux4-remediacion.md`:

- centralizar calculo de division;
- tests que cubran alta/edicion o helper realmente usado;
- verificar sincronizacion de `gasto_participantes` y `gasto_divisiones`;
- correr suite completa.

### Fase 2 - Limpieza segura

En commit separado:

- borrar `_division_modal.php`;
- borrar `Home.php`;
- borrar metodos muertos de `Gasto`, `GastoDivision`, `UserPaymentMethod` y
  `User::getRole()`;
- correr `rg` para confirmar cero referencias;
- correr `php -l` en PHP tocados;
- correr `php vendor/bin/phpunit --no-coverage`.

### Fase 3 - Limpieza con tests

Solo si se quiere reducir mas:

- decidir si se eliminan helpers testeados de `User`, `Reportes`,
  `PasswordReset` y `Grupo::getPermisos()`;
- actualizar o borrar tests correspondientes conscientemente;
- no hacerlo como "delete automatico".

## Verificaciones

Antes de cerrar una limpieza Ponytail:

```bash
rg -n "nombreDelMetodoOBorrado" app tests
php -l app/Models/Gasto.php
php -l app/Models/GastoDivision.php
php -l app/Models/UserPaymentMethod.php
php -l app/Models/User.php
php vendor/bin/phpunit --no-coverage
git diff --check
git status --short
```

## Criterio de cierre

- No quedan referencias a archivos/metodos borrados.
- Tests pasan.
- La limpieza no toca comportamiento de UX4, balance, pagos ni permisos.
- El diff es claramente de eliminacion o simplificacion.

