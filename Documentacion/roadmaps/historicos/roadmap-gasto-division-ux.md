# Roadmap Gasto Division UX

Actualizado: 2026-06-16.

Este documento registra ajustes pendientes sobre la experiencia de division en
la pantalla de nuevo/editar gasto. No ejecutar todavia sin confirmacion.

## Contexto

UX4 dejo la logica contable de division funcionando y centralizada. La pantalla
de gasto ahora soporta:

- pagador editable;
- division igualitaria;
- monto fijo;
- porcentaje;
- opciones rapidas visuales.

Pero la zona de opciones rapidas todavia no se siente natural: ocupa demasiado
espacio, se ve como una lista pesada y corta el flujo de carga rapida.

## Objetivo

Mantener la carga rapida de gasto simple:

1. descripcion;
2. monto;
3. resumen de division;
4. crear gasto.

La configuracion de division debe sentirse secundaria, accionable y clara,
similar al patron de Splitwise: tocar el resumen abre una interaccion dedicada
para elegir como se divide.

## Problema actual

En `app/Views/gastos/form.php`, dentro de `Division del gasto`, se ven al mismo
tiempo:

- `Pagado por`;
- resumen "Por defecto, dividido en partes iguales";
- lista de presets rapidos;
- boton `Mas opciones de division`;
- modalidad;
- participantes.

Esto genera demasiada informacion en la pantalla principal de alta.

## Decision UX preferida

La pantalla principal NO deberia mostrar la lista completa de presets rapidos.

En su lugar:

- Mostrar solo una card/resumen de division.
- Esa card debe decir el estado actual, por ejemplo:
  - `Dividido en partes iguales`;
  - `Fernando pago, dividido en partes iguales`;
  - `Antonella pago, dividido en partes iguales`;
  - `Fernando pago todo`;
  - `Monto fijo`;
  - `Porcentaje`.
- Al tocar esa card/resumen, abrir una interaccion secundaria para elegir el
  modo.

## Opciones de implementacion

### Opcion A - Modal inferior / modal Bootstrap

Accion:

- Tocar la card "Por defecto, dividido en partes iguales" abre un modal.

Contenido del modal:

- Titulo: `Como se dividio este gasto?`
- Opciones tipo lista:
  - `Partes iguales`;
  - `Yo pague, me deben`;
  - `{otro miembro} pago`;
  - `Le debo a {otro miembro}`;
  - `Monto fijo`;
  - `Porcentaje`.
- Opcion activa marcada con check o estado visual.
- Boton cerrar/volver.

Ventajas:

- Muy similar a Splitwise.
- Mantiene la pantalla principal limpia.
- No mezcla presets con formulario avanzado.

Riesgos:

- Requiere cuidar estado JS y hidden inputs.
- En mobile debe sentirse como panel/bottom sheet o modal simple.

### Opcion B - Desplegable colapsable

Accion:

- Tocar la card/resumen abre un collapse debajo.

Contenido:

- Misma lista de opciones.
- `Mas opciones de division` queda dentro del collapse o al final.

Ventajas:

- Menos complejidad que modal.
- Sigue el patron exitoso de `Mas acciones`.

Riesgos:

- Puede volver a ocupar mucho espacio si la lista es larga.

### Opcion C - Select visual

Accion:

- La card/resumen se reemplaza por un select estilizado.

Ventajas:

- Simple tecnicamente.

Riesgos:

- Menos parecido a Splitwise.
- Peor para explicar escenarios como "Yo pague, me deben".

## Preferencia actual

Preferencia: **Opcion A - Modal / ventana secundaria**.

Si se busca algo mas rapido: **Opcion B - Desplegable colapsable** usando el
mismo patron visual de `Mas acciones`.

No usar nuevamente una lista larga siempre visible en la pantalla principal.

## Reglas de comportamiento

- Default al crear gasto:
  - `division_tipo = igualitario`;
  - todos los miembros seleccionados;
  - pagador por defecto = usuario logueado.
- La pantalla principal debe mostrar solo el resumen.
- Las opciones rapidas modifican:
  - `pagador_id`;
  - `participantes[]`;
  - `division_tipo`;
  - `division_valores` cuando corresponda.
- El backend sigue siendo fuente de validacion.
- `Monto fijo` y `Porcentaje` pueden abrir la seccion avanzada o una segunda
  vista dentro del modal.

## Fuera de alcance

- No cambiar calculo contable.
- No cambiar `Gasto::calcularMontosDivision()`.
- No cambiar migraciones.
- No tocar balance.
- No tocar reportes.
- No hacer limpieza Ponytail en esta tarea.

## Criterios de aceptacion

- La pantalla principal de nuevo gasto queda limpia.
- No se ve una lista larga de presets en la pantalla principal.
- Tocar el resumen de division permite elegir otra forma de division.
- El estado elegido se refleja en el resumen.
- El submit envia los mismos campos esperados por el backend.
- `Mas opciones de division` sigue disponible para casos avanzados.
- Mobile 360px no tiene textos desbordados ni botones crudos.

## Verificaciones cuando se implemente

```bash
php -l app/Views/gastos/form.php
php vendor/bin/phpunit --no-coverage
git diff --check
```

QA visual:

- Nuevo gasto sin monto.
- Nuevo gasto con monto.
- Elegir cada preset.
- Abrir/cerrar modal o collapse.
- Abrir `Mas opciones de division`.
- Editar gasto existente con division no igualitaria.

