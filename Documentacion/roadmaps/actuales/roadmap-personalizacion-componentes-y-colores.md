# Roadmap: componentes avanzados seleccionables y personalizacion visual

## Estado del documento

Este roadmap reconstruye el plan activo para evolucionar el catalogo visual de SplitWise hacia un sistema donde la app pueda decidir que componente mostrar, como mostrarlo y en que accion o contexto usarlo.

El objetivo no es solo elegir tarjetas desde un catalogo. El objetivo es que los componentes visuales puedan convertirse en piezas reales de la aplicacion, asignables a acciones concretas, configurables por defecto y personalizables por usuario o grupo.

## Problema

El catalogo actual permite ver variantes visuales, seleccionar disenos y marcar decisiones de implementacion. Eso sirve para curar componentes, pero todavia no resuelve completamente estos casos:

- Una misma familia visual puede necesitar multiples variantes activas al mismo tiempo.
- Las alertas de exito, error o advertencia no deberian tener una unica variante global.
- Una accion de la app necesita saber que componente debe renderizar.
- Un grupo o usuario podria necesitar preferencias visuales propias.
- El color de los movimientos deberia poder personalizarse sin romper el estilo global.
- Si toda la logica vive dentro de una vista gigante, borrar o reemplazar un componente se vuelve costoso.

Ejemplo claro: una alerta de exito puede aparecer cuando se crea un grupo, se registra un gasto, se crea un pago, se edita un medio de cobro o se cierra un grupo. No alcanza con elegir "la alerta de exito activa"; hay que mapear acciones reales de la app a componentes visuales concretos.

## Objetivo general

Crear un sistema de componentes seleccionables, parametrizables y asignables por accion.

El sistema deberia permitir:

- Definir componentes visuales reutilizables.
- Definir acciones reales de la aplicacion.
- Relacionar componentes con acciones.
- Resolver que componente renderizar para una accion especifica.
- Aplicar configuraciones globales, por pantalla, por grupo y por usuario.
- Personalizar colores de movimientos dentro de un grupo.
- Mantener cada componente en su propio archivo para facilitar altas, bajas y cambios.

## Principio de diseno

El modelo debe separar tres preguntas:

1. Que se muestra.
2. Como se muestra.
3. Donde y cuando se muestra.

"Que se muestra" corresponde a los datos: gasto creado, pago registrado, deuda pendiente, movimiento del grupo, cierre de grupo.

"Como se muestra" corresponde al componente y sus parametros: tarjeta compacta, alerta, item de movimiento, badge, color, icono, densidad visual.

"Donde y cuando se muestra" corresponde a la accion o contexto: crear gasto, editar pago, ver movimientos de grupo, confirmar borrado, mostrar feedback de error.

## Modelo conceptual

### Componente

Representa una pieza visual disponible.

Ejemplos:

- Tarjeta de grupo.
- Total filtrado.
- Gauge de grupo.
- Alerta de exito.
- Alerta de error.
- Confirmacion destructiva.
- Item de movimiento.
- Badge de estado.

Cada componente deberia vivir en un archivo propio o en una carpeta propia si tiene multiples variantes.

Campos conceptuales:

- component_key.
- nombre.
- descripcion.
- tipo.
- slot visual.
- variantes disponibles.
- parametros esperados.
- estado: activo, experimental, descartado.

### Accion

Representa un evento o contexto real de la aplicacion.

Ejemplos:

- auth.login.failed.
- groups.create.completed.
- groups.close.requested.
- groups.close.completed.
- expenses.create.completed.
- expenses.update.failed.
- payments.create.completed.
- payments.delete.requested.
- movements.group.list.item.
- movements.payment.list.item.

Campos conceptuales:

- action_key.
- dominio: auth, groups, expenses, payments, profile, reports.
- tipo: success, error, warning, info, confirmation, movement.
- pantalla o modulo.
- descripcion.
- parametros disponibles.
- prioridad.
- estado: activa, pendiente, descartada.

### Relacion componente-accion

Define que componente se usa para una accion determinada.

Campos conceptuales:

- action_key.
- component_key.
- variant_key.
- slot.
- parametros por defecto.
- prioridad.
- scope.
- scope_id.
- activo.

El scope permite resolver configuraciones con jerarquia.

## Jerarquia de configuracion

La resolucion deberia aplicar una cadena de prioridad, de mas especifico a mas general:

1. Override del usuario para un grupo especifico.
2. Override del grupo.
3. Override de la pantalla o modulo.
4. Configuracion especifica de la accion.
5. Configuracion del tipo de accion.
6. Default global del slot.
7. Fallback hardcodeado seguro.

Ejemplo:

- Si el grupo "Viaje Bariloche" eligio color violeta para movimientos, se usa violeta.
- Si no eligio nada, se usa el color del usuario.
- Si el usuario no eligio nada, se usa la paleta global.
- Si no hay paleta global, se usa celeste por defecto.

## Personalizacion de colores

### Objetivo

Permitir que el usuario elija como visualizar los movimientos dentro de un grupo, sin que cada pantalla tenga estilos aislados.

El caso inicial es color de movimientos.

Default propuesto:

- Celeste como color base.

Paleta inicial sugerida:

- Celeste.
- Verde.
- Violeta.
- Azul.
- Amarillo.
- Rojo suave.
- Gris neutro.

La seleccion deberia aplicarse a:

- Movimientos del grupo.
- Gastos dentro del grupo.
- Pagos dentro del grupo.
- Badges o acentos relacionados a actividad.
- Graficos o indicadores si corresponden.

### Niveles de personalizacion

Nivel global:

- Define la paleta base de la app.
- Define defaults de componentes.

Nivel usuario:

- Preferencia visual general.
- Puede definir color preferido para movimientos.

Nivel grupo:

- Color especifico para ese grupo.
- Tiene prioridad sobre la preferencia global del usuario en pantallas del grupo.

Nivel accion:

- Algunas acciones pueden forzar color semantico.
- Ejemplo: error siempre rojo, warning siempre amarillo.

## Acciones y feedback

El sistema de feedback debe trabajar sobre acciones, no solo sobre tipos generales.

Ejemplos:

- groups.create.completed.
- groups.update.completed.
- groups.close.requested.
- groups.close.completed.
- groups.close.cancelled.
- groups.close.failed.
- expenses.create.completed.
- expenses.create.failed.
- payments.create.completed.
- payments.delete.requested.
- payments.delete.cancelled.
- payments.delete.completed.

Para acciones destructivas:

- Mostrar modal o popup centrado y cuidado.
- Pedir confirmacion explicita.
- Si acepta y sale bien: alerta de exito.
- Si cancela: alerta informativa o neutral.
- Si falla: alerta de error.

Ejemplo:

- Confirmacion: "Estas por eliminar este pago."
- Cancelacion: "Se cancelo la accion."
- Exito: "El pago se elimino correctamente."
- Error: "No se pudo eliminar el pago."

## Parametros de componentes

Los componentes no deberian tener textos fijos cuando representan acciones reutilizables.

Deberian aceptar parametros como:

- title.
- message.
- entity_name.
- amount.
- date.
- user_name.
- group_name.
- action_label.
- color.
- icon.
- tone.

Ejemplo:

Template:

`{entity} se completo con exito.`

Parametros:

- entity = "El pago".

Resultado:

`El pago se completo con exito.`

## Estructura tecnica propuesta

### Archivos de componentes

Mantener componentes en archivos independientes:

`app/Catalog/Components/{dominio}/{component_key}.php`

Para componentes con muchas variantes:

`app/Catalog/Components/{dominio}/{component_key}/index.php`

`app/Catalog/Components/{dominio}/{component_key}/variants/{variant_key}.php`

Cada archivo deberia devolver metadata y una funcion de render o estructura renderizable.

### Configuracion

Mantener una configuracion inicial en PHP para defaults:

- `app/Config/UiFeedback.php`
- futuro: `app/Config/UiComponents.php`
- futuro: `app/Config/UiActions.php`

La base de datos deberia guardar overrides y decisiones persistentes.

### Servicios

Servicios propuestos:

- `UiComponentResolver`.
- `UiFeedbackResolver`.
- `UiActionResolver`.
- `UiPreferenceResolver`.
- `GroupVisualPreferenceResolver`.

El resolver final deberia poder responder:

`Dada esta accion, este usuario y este grupo, que componente y variante uso?`

## Tablas propuestas

### ui_components

Catalogo de componentes disponibles.

Campos posibles:

- id.
- component_key.
- name.
- description.
- type.
- slot.
- status.
- created_at.
- updated_at.

### ui_actions

Catalogo de acciones reales de la aplicacion.

Campos posibles:

- id.
- action_key.
- domain.
- action_type.
- screen_key.
- description.
- default_slot.
- active.
- created_at.
- updated_at.

### ui_component_action_rules

Relacion entre accion y componente.

Campos posibles:

- id.
- action_key.
- component_key.
- variant_key.
- slot.
- scope.
- scope_id.
- params_json.
- priority.
- active.
- created_at.
- updated_at.

### ui_group_visual_preferences

Preferencias visuales por grupo.

Campos posibles:

- id.
- group_id.
- movement_color.
- palette_key.
- component_preferences_json.
- created_at.
- updated_at.

### ui_user_visual_preferences

Preferencias visuales por usuario.

Campos posibles:

- id.
- user_id.
- movement_color.
- palette_key.
- created_at.
- updated_at.

## MVP propuesto

### Fase 1: mapa de acciones

Objetivo:

Identificar todas las acciones reales que hoy disparan feedback o cambios visuales.

Alcance:

- Revisar controllers.
- Listar POST/PUT/DELETE reales.
- Separar acciones de exito, error, warning, confirmacion y cancelacion.
- Documentar parametros disponibles para cada accion.

Salida esperada:

- `Documentacion/roadmaps/actuales/mapa-acciones-ui.md`
- Lista de action_key normalizados.
- Acciones candidatas a feedback.
- Acciones candidatas a confirmacion modal.
- Acciones candidatas a personalizacion visual.

### Fase 2: resolver por accion

Objetivo:

Extender el feedback actual para que resuelva componente, variante y texto por action_key.

Alcance:

- Mantener fallback seguro.
- No romper flashdata actual.
- No renderizar desde el controller.
- Preparar payload renderizable.

### Fase 3: alertas por accion

Objetivo:

Permitir que distintas acciones de exito usen distintas variantes de alerta.

Ejemplo:

- expenses.create.completed usa una variante.
- payments.create.completed usa otra.
- groups.close.completed usa otra.

La seleccion no debe pisarse entre contextos.

### Fase 4: confirmaciones destructivas

Objetivo:

Crear un componente de confirmacion reutilizable.

Debe soportar:

- aceptar.
- cancelar.
- feedback posterior.
- tono destructivo.
- texto parametrizable.

Acciones candidatas:

- eliminar gasto.
- eliminar pago.
- quitar miembro.
- eliminar medio de cobro.
- cerrar grupo si corresponde.

### Fase 5: colores de movimientos por grupo

Objetivo:

Agregar preferencia de color para movimientos del grupo.

Alcance inicial:

- Campo de configuracion visual en grupo.
- Paleta limitada.
- Aplicar color a movimientos en la pantalla de grupo.
- Respetar fallback celeste.

### Fase 6: preferencias por usuario

Objetivo:

Permitir defaults visuales del usuario.

Alcance:

- Preferencia de paleta.
- Preferencia de densidad o estilo si aplica.
- Prioridad menor que override de grupo.

### Fase 7: administracion desde catalogo

Objetivo:

Que el catalogo deje de ser solo preview y pueda administrar relaciones reales.

Debe permitir:

- Ver acciones.
- Ver componente asignado a cada accion.
- Cambiar componente/variante.
- Ver herencia y overrides.
- Resetear a default.

## Reglas de compatibilidad

- Ninguna migracion debe borrar preferencias existentes sin migracion explicita.
- Toda accion debe tener fallback.
- Si una accion no tiene componente asignado, se usa slot default.
- Si falla el render de un componente, se muestra mensaje simple.
- Los colores semanticos de error y warning no deben ser reemplazados por paletas decorativas.
- Los componentes deben poder recibir parametros sin consultar la base de datos por su cuenta.

## Riesgos

- Sobredisenar antes de tener mapa completo de acciones.
- Duplicar logica entre frontend y backend.
- Convertir cada pantalla en un caso especial.
- Mezclar seleccion visual con reglas de negocio.
- Guardar HTML completo en base de datos.
- Permitir overrides sin fallback claro.

## Decisiones tomadas

- El codigo del componente debe vivir en archivos PHP, no como HTML completo en base de datos.
- La base de datos debe guardar seleccion, relacion, parametros y preferencias.
- Las alertas necesitan seleccion por accion, no una unica seleccion global.
- Los movimientos deben soportar personalizacion de color por grupo.
- El color por defecto para movimientos es celeste.
- Los componentes deben ser parametrizables.
- El catalogo debe evolucionar hacia una herramienta de administracion real.

## Preguntas pendientes

- Que acciones exactas entran en el MVP?
- La personalizacion por grupo la puede cambiar cualquier miembro o solo admin/creador?
- Los colores son por grupo completo o por usuario dentro de cada grupo?
- La paleta debe ser fija o administrable?
- Las alertas se renderizan via flashdata actual o con un componente nuevo centralizado?
- Donde conviene guardar params_json: relacion, accion o preferencia?
- Conviene exponer esto en admin primero y luego en usuario final?

## Criterios de listo

El roadmap se considera implementado cuando:

- Existe un mapa de acciones versionado.
- Cada accion importante tiene action_key.
- El feedback puede resolver componente por accion.
- Las alertas de exito pueden variar por accion sin pisarse.
- Las confirmaciones destructivas usan un componente reutilizable.
- Los movimientos de grupo pueden tomar color personalizado.
- Existe fallback global.
- El catalogo permite inspeccionar o modificar estas relaciones.

## Orden recomendado de trabajo

1. Auditar acciones actuales.
2. Definir action_key canonicos.
3. Crear mapa de acciones.
4. Extender resolver de feedback.
5. Asignar alertas por accion.
6. Crear confirmacion destructiva.
7. Agregar preferencias visuales de grupo.
8. Aplicar color en movimientos.
9. Integrar administracion en catalogo.
10. Documentar decisiones y defaults.
