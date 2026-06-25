# Flujos principales

## Crear grupo

1. Ir a /grupos/nuevo.
2. Completar nombre y descripcion (opcional).
3. Seleccionar miembros iniciales de la lista de usuarios disponibles.
4. Enviar formulario. El usuario creador queda como admin del grupo.
5. Redirige a /grupos con mensaje de exito.

## Agregar miembros

1. Entrar a edicion del grupo (/grupos/{id}/editar).
2. Seleccionar usuario de la lista de disponibles.
3. Enviar formulario. El nuevo miembro se agrega con rol "member".
4. No se pueden agregar admins globales como miembros de grupo.

## Registrar gasto

1. Ir a /gastos/nuevo?grupo_id={id}.
2. Seleccionar grupo (si no viene pre-seleccionado).
3. Ingresar monto con formato argentino (ej: 1.500,00).
4. Seleccionar participantes (al menos 1).
5. Elegir tipo de division: igualitario, monto fijo o porcentaje.
6. Opcional: categoria, nota, adjuntar recibo.
7. Enviar. Redirige al detalle del grupo.

## Registrar pago

1. Ir a /pagos/nuevo?grupo_id={id} (o desde balance de grupo).
2. Seleccionar grupo.
3. Seleccionar receptor (quien recibe el dinero).
4. Ingresar monto.
5. Opcional: descripcion.
6. Enviar. Redirige segun origen del formulario.

## Ver balance de grupo

1. Ir a /grupos/{id} para ver actividad.
2. Ir a /grupos/{id}/balance para ver detalle de deudas.
3. Muestra: total gastado, total pagado, deudas entre miembros, gastos por categoria.
4. Incluye acceso a medios de cobro de los acreedores.

## Cerrar/reabrir/liquidar grupo

1. Ir a edicion del grupo (/grupos/{id}/editar).
2. Usar boton de accion correspondiente (Cerrar, Reabrir, Liquidar).
3. Confirmar en modal.
4. Liquidar solo disponible si no hay deudas pendientes.
5. Un grupo liquidado no permite nuevas operaciones.
