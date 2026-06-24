# Roadmap UX4 Remediacion - Division de gastos

Actualizado: 2026-06-16.

## Estado de ejecucion

Fase 1 completada en la rama `feature/division-gastos-unificada`.

Commits relevantes:

- `aa6d0a6` - corrige el orden de `$divisionTipo` y `$divisionValores` en
  `Gastos::create()`.
- `517efd0` - extrae `Gasto::calcularMontosDivision()` y lo reutiliza desde
  `create()` y `update()`.

Verificacion local posterior:

- `php vendor/bin/phpunit --no-coverage`: OK, 233 tests, 511 assertions.
- `Gastos::create()` y `Gastos::update()` llaman al mismo calculador.
- `getBalanceByGrupo()` sigue leyendo `gasto_divisiones.monto_calculado` como
  fuente primaria y usa `gasto_participantes` solo como fallback para gastos sin
  filas en `gasto_divisiones`.

Pendientes residuales:

- Agregar tests de validaciones invalidas del controller o de un validador
  centralizado: monto fijo que no suma, porcentaje distinto de 100,
  `division_valores` con usuario fuera de participantes y valor negativo.
- Refactor corto post-review Ponytail:
  - extraer validacion duplicada de `division_valores` en `Gastos::create()` y
    `Gastos::update()` a un metodo privado tipo `validarDivision()`;
  - eliminar `$valoresNormalizados` en `create()`, porque se construye y no se
    usa;
  - opcionalmente extraer la insercion de `gasto_divisiones` si no aumenta
    acoplamiento;
  - mantener `Gasto::calcularMontosDivision()` defensivo, aunque normalice ids,
    salvo que todos sus tests y callers garanticen ids ya normalizados.
- Decidir en una fase posterior si balance, reportes y pantallas deben leer de
  una unica tabla (`gasto_participantes` o `gasto_divisiones`).
- Ejecutar la limpieza Ponytail segura definida en
  `roadmap-ponytail-cleanup.md`, en commit separado.

Este documento baja a plan accionable la auditoria de UX4 sobre la rama
`feature/division-gastos-unificada`. No reemplaza `roadmap-visuales.md`; lo
complementa para corregir los hallazgos detectados antes de PR/merge.

## Objetivo

Dejar UX4 lista para revision corrigiendo el bug critico de alta de gastos con
division no igualitaria, reduciendo el riesgo de doble fuente de verdad y
agregando tests que ejerciten la logica real en vez de una copia aislada.

## Estado actual confirmado

- `Gastos::create()` usa `$divisionTipo` y `$divisionValores` antes de
  inicializarlas.
- `Gastos::update()` define esas variables antes del calculo y no presenta el
  mismo bug.
- Los tests actuales pasan, pero `DivisionGastosTest` replica el algoritmo en
  el test y no cubre el flujo real del controller.
- `getBalanceByGrupo()` consume primero `gasto_divisiones.monto_calculado`.
- Algunos reportes consumen `gasto_participantes.monto_asignado`.
- Por lo tanto, ambas tablas deben escribirse desde un mismo calculo y no pueden
  divergir.

## Prioridad 0 - Preparacion

1. Revisar `git status --short --branch`.
2. Identificar cambios no relacionados ya presentes en el working tree.
3. No revertir cambios ajenos.
4. Mantener esta remediacion separada de ajustes visuales de grupo u otras UX.
5. Si se va a commitear, separar en commits por tema:
   - fix de calculo/persistencia;
   - tests;
   - limpieza/documentacion si aplica.

## Prioridad 1 - Bug bloqueante en alta de gasto

### Problema

En `app/Controllers/Gastos.php`, metodo `create()`, el calculo de
`$participantesMonto` ocurre antes de leer:

```php
$divisionTipo = $this->request->getPost('division_tipo') ?: 'igualitario';
$divisionValores = $this->request->getPost('division_valores') ?? [];
```

### Resultado esperado

Crear un gasto con:

- `division_tipo = igualitario` guarda montos igualitarios y suma el total.
- `division_tipo = monto_fijo` guarda exactamente los montos enviados, si suman
  el total.
- `division_tipo = porcentaje` calcula montos proporcionales y ajusta redondeo
  en el ultimo participante para conservar el total.

### Accion minima aceptable

Mover la lectura y validacion de `$divisionTipo` y `$divisionValores` antes de
cualquier calculo de `$participantesMonto` en `create()`.

### Accion preferida

Extraer un calculador unico reutilizable por `create()` y `update()`, por
ejemplo:

```php
private function calcularMontosDivision(
    string $divisionTipo,
    float $monto,
    array $participantesIds,
    array $divisionValores
): array
```

Debe devolver:

```php
[
    user_id => monto_calculado,
]
```

Reglas:

- Igualitario: divide en partes iguales y ajusta redondeo en el ultimo usuario.
- Monto fijo: usa los valores normalizados por usuario.
- Porcentaje: calcula monto * porcentaje / 100 y ajusta redondeo en el ultimo
  usuario.
- No debe validar permisos ni pertenencia al grupo; esas validaciones quedan en
  el controller.

## Prioridad 2 - Validacion server-side

Mantener o mejorar las validaciones existentes en `create()` y `update()`:

- tipo de division en `igualitario`, `monto_fijo`, `porcentaje`;
- pagador pertenece al grupo;
- cada participante pertenece al grupo;
- al menos un participante;
- para modos no igualitarios, `division_valores` tiene la misma cantidad de
  usuarios que participantes;
- los usuarios de `division_valores` coinciden exactamente con participantes;
- no hay valores negativos;
- `monto_fijo` suma el total del gasto con tolerancia razonable;
- `porcentaje` suma 100 con tolerancia razonable.

No confiar en JavaScript para validar. JavaScript solo previsualiza.

## Prioridad 3 - Persistencia sin divergencia

Al crear o editar un gasto, ambos destinos deben usar el mismo mapa calculado:

- `gasto_participantes.monto_asignado`;
- `gasto_divisiones.monto_calculado`.

Reglas para `gasto_divisiones`:

- `tipo` guarda el modo seleccionado.
- `valor` guarda el valor original por usuario para `monto_fijo` y
  `porcentaje`.
- `valor` puede quedar `null` para `igualitario`.

No cambiar migraciones ni eliminar tablas en esta remediacion.

## Prioridad 4 - Tests que detecten el bug real

### Problema actual

`tests/unit/DivisionGastosTest.php` tiene un metodo privado que copia el
algoritmo. Eso no detecta errores de orden en `Gastos::create()`.

### Objetivo de cobertura

Agregar tests que fallen con el bug actual y pasen con el fix.

Opcion preferida:

- Feature/integration tests del flujo real de alta de gasto, si la configuracion
  de CodeIgniter lo permite sin fragilidad excesiva.

Opcion aceptable:

- Tests unitarios sobre el helper/metodo centralizado de calculo, siempre que
  `create()` y `update()` usen ese helper. No copiar el algoritmo dentro del
  test.

### Casos minimos

1. `monto_fijo`:
   - total `100`;
   - participantes `1`, `2`;
   - valores `30`, `70`;
   - resultado `30`, `70`.

2. `porcentaje`:
   - total `200`;
   - participantes `1`, `2`;
   - valores `60`, `40`;
   - resultado `120`, `80`.

3. `porcentaje` con redondeo:
   - total `100`;
   - participantes `1`, `2`, `3`;
   - valores `33.33`, `33.33`, `33.34`;
   - suma final exacta `100`.

4. Validacion invalida:
   - `monto_fijo` que no suma el total se rechaza;
   - `porcentaje` que no suma 100 se rechaza;
   - `division_valores` con usuario fuera de participantes se rechaza;
   - valor negativo se rechaza.

## Prioridad 5 - Fuente de verdad operativa

Decision para esta remediacion:

- No hacer migracion destructiva.
- Tratar el calculo centralizado como fuente de verdad en runtime.
- Escribir ambas tablas desde ese calculo.
- Evitar que `gasto_participantes` y `gasto_divisiones` puedan calcularse por
  caminos separados.

Pendiente para una fase futura:

- Decidir si el balance, reportes y pantallas deben leer todos de
  `gasto_participantes` o todos de `gasto_divisiones`.
- Documentar esa decision y migrar consultas si corresponde.

## Prioridad 6 - Limpieza opcional

`app/Models/GastoDivision.php` contiene modos `partes` y `ajuste`, no expuestos
en UX4.

Accion recomendada:

- No usarlos desde controller ni UI.
- Si se toca el modelo, se pueden remover del calculador activo.
- Si removerlos aumenta riesgo, dejarlos para una limpieza posterior y cubrir
  que los tipos validos del controller sigan siendo solo:
  - `igualitario`;
  - `monto_fijo`;
  - `porcentaje`.

## Verificaciones obligatorias

Ejecutar antes de dar por cerrada la remediacion:

```bash
php -l app/Controllers/Gastos.php
php -l app/Models/GastoDivision.php
php vendor/bin/phpunit --no-coverage
git diff --check
git status --short
```

Si se tocaron otros PHP, ejecutar `php -l` tambien sobre esos archivos.

Si MySQL/XAMPP esta disponible:

```bash
php spark migrate:status
php spark routes
```

## Criterios de aceptacion

- Crear gasto con `monto_fijo` preserva los montos reales.
- Crear gasto con `porcentaje` preserva los montos calculados reales.
- Editar gasto sigue preservando division.
- `gasto_participantes` y `gasto_divisiones` quedan sincronizadas por el mismo
  mapa de montos.
- Balance de grupo refleja la division real de gastos nuevos.
- Tests nuevos fallarian con el bug original y pasan con el fix.
- No se introducen modos visibles fuera de alcance.
- No se mezclan cambios de UX3/grupos u otras fases dentro del commit de UX4.

## Entrega esperada

Al finalizar, informar:

- archivos modificados;
- resumen del fix;
- tests agregados o modificados;
- resultado de verificaciones;
- riesgos residuales;
- si quedan cambios no relacionados en el working tree.
