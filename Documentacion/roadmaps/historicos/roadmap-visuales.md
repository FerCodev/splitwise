# Roadmap Visuales y Refinamiento Funcional - SplitWise

Documento tematico independiente. No reemplaza `Roadmap.md` ni `Roadmap_Reportes_Analytics.md`.

Este roadmap es la fuente operativa para refinamientos visuales y funcionales pendientes. UX1, UX2, UX3, UX5, UX6 y UX7 ya fueron implementadas en una rama integral y mergeadas. La proxima fase ejecutable es UX4, que queda separada porque toca division de gastos, validaciones backend y calculo contable.

## Estado actual

Actualizado: 2026-06-15.

Completado y mergeado:

- UX1 - Racionalizar navegacion.
- UX2 - Movimientos clickeables en Home.
- UX3 - Redisenio de pantalla de Grupo.
- UX5 - Perfil: editar email.
- UX6 - Medios de cobro simplificados.
- UX7 - Balance: boton pagar segun permisos.

Pendiente:

- UX4 - Unificar division de gastos.

UX4 debe ejecutarse en una rama independiente y con auditoria propia antes del merge, porque modifica el formulario de gasto, la forma en que se calculan/guardan participantes y la fuente de verdad usada por el balance.

## Estrategia de ejecucion

- Rama ya cerrada para UX1/UX2/UX3/UX5/UX6/UX7: `feature/visuales-refinamiento-integral`
- Rama recomendada para UX4: `feature/division-gastos-unificada`
- Base: `main` actualizado con `origin/main`
- No reabrir la rama integral salvo para auditoria historica.
- UX4 se trabaja como fase nueva desde `main`.
- Hacer commits separados por capa: UI, validacion/calculo, persistencia, tests.
- Abrir PR contra `main` al finalizar UX4.
- No mergear sin autorizacion explicita.
- No tocar produccion/deploy.
- No usar `Roadmap.md` para decidir alcance de esta tarea; queda como historico general.

Orden historico:

1. UX1 - Racionalizar navegacion - COMPLETADA
2. UX2 - Movimientos clickeables en Home - COMPLETADA
3. UX3 - Redisenio de pantalla de Grupo - COMPLETADA
4. UX5 - Perfil: editar email - COMPLETADA
5. UX6 - Medios de cobro simplificados - COMPLETADA
6. UX7 - Balance: boton pagar segun permisos - COMPLETADA
7. UX4 - Unificar division de gastos - PENDIENTE

UX4 queda separada porque toca logica contable y debe auditarse con mas cuidado.

## Estado actual relevante

Verificado contra el codigo real y contra el uso actual de la app:

- Navegacion: hay redundancia entre hamburguesa superior y menu "Mas" del bottom tab. Reportes y Medios de cobro aparecen en mas de un lugar.
- Home: el feed de movimientos se ve bien, pero sus items no siempre llevan al detalle del gasto o pago.
- Grupo: la pantalla del grupo tiene demasiadas acciones visibles en el header. La gestion de miembros ensucia la vista operativa del grupo.
- Gasto: la carga rapida mejoro, pero la division de gastos todavia mezcla varias ideas: pagador, participantes igualitarios y division avanzada.
- Perfil: permite editar nombre y password, pero no email.
- Medios de cobro: la pantalla quedo visualmente vieja y pide mas datos de los necesarios.
- Balance: el boton de pagar aparece para deudas que no corresponden al usuario logueado.

## Decisiones de producto

### Navegacion

- Bottom tab mobile debe quedar limpio y orientado a uso diario.
- Propuesta de tabs: Home, Grupos, Gastos, Reportes, Perfil.
- El menu hamburguesa superior queda como menu secundario y administrativo.
- Dentro del menu hamburguesa van: Medios de cobro, Pagos, Usuarios, Categorias, Documentacion si existe, Cerrar sesion.
- Si se conserva el tab "Mas", no debe duplicar opciones con la hamburguesa. La preferencia de este roadmap es quitar "Mas" del bottom tab y dejar la hamburguesa como unico menu secundario.

### Grupo

- La pantalla de grupo debe priorizar el flujo diario: ver saldo, movimientos y agregar gasto.
- Las acciones administrativas del grupo se mueven a "Ver grupo" o "Configurar grupo".
- Desde esa pantalla de configuracion se administra: nombre, descripcion, estado y miembros.
- La gestion de miembros no debe vivir en la misma pantalla operativa donde se ven gastos y pagos.

### Gasto y division

- La carga principal de gasto debe ser rapida.
- Campos principales visibles: descripcion, monto, fecha.
- Grupo implicito si el gasto se crea desde un grupo.
- Pagador editable: por defecto el usuario logueado, pero se puede elegir otro miembro del grupo.
- Categoria inferida por diccionario en vivo a partir de la descripcion. La categoria puede quedar como dato visible/indicador, no como selector principal.
- Division visible como una sola seccion: "quien paga" y "como se divide".
- Modos soportados para esta etapa: igualitario, porcentaje, monto fijo.
- No implementar ni exponer "partes/cuotas" ni "ajuste" en esta etapa.

### Division y fuente de verdad

La division de gastos no puede tener dos fuentes de verdad contradictorias.

Regla para esta rama:

- `gasto_participantes.monto_asignado` es la fuente que debe reflejar la division real, porque el balance se apoya en esos montos.
- `gastos.division_tipo` se conserva para recordar la modalidad al editar.
- Si existe `gasto_divisiones`, no borrar tabla ni migraciones en esta rama. Puede mantenerse como espejo/auditoria solo si se escribe con los mismos montos calculados. No debe contradecir a `gasto_participantes`.
- No hacer migraciones destructivas para eliminar columnas/tablas salvo que el roadmap lo pida explicitamente. Este roadmap no lo pide.

### Balance

- Usuario comun: ve y puede pagar solo sus propias deudas.
- Admin de grupo: puede ver todo el balance.
- Para esta etapa, evitar "pagar en nombre de otro" como feature general salvo que ya exista permiso claro y backend consistente.
- No basta con ocultar botones: backend debe validar que el usuario puede registrar ese pago.

## UX1 - Racionalizar navegacion

### Objetivo

Eliminar duplicacion entre bottom tab y hamburguesa, dejando una navegacion mobile simple y una navegacion secundaria clara.

### Alcance

- Ajustar `app/Views/partials/_navbar.php`.
- Ajustar `public/assets/app.css` si hace falta.
- Bottom tab mobile propuesto: Home, Grupos, Gastos, Reportes, Perfil.
- Menu hamburguesa superior: opciones secundarias y admin.
- Reportes debe quedar como acceso principal o claramente accesible desde bottom tab.
- Medios de cobro debe salir de los botones principales de Home y vivir en el menu secundario o Perfil.
- Navbar desktop debe seguir funcional.

### Fuera de alcance

- No cambiar rutas.
- No cambiar controllers.
- No cambiar permisos.

### Criterios de aceptacion

- No hay destinos duplicados entre bottom tab y hamburguesa.
- Todas las rutas siguen accesibles.
- Mobile y desktop siguen navegables.
- Login y pantallas publicas no muestran navegacion privada.

### Commit sugerido

`ux: racionaliza navegacion mobile`

## UX2 - Movimientos clickeables en Home

### Objetivo

Que cada movimiento del feed de inicio lleve al detalle real del gasto o pago.

### Alcance

- En `app/Views/dashboard.php`, cada item de actividad reciente debe ser un enlace.
- Si es gasto: `/gastos/{id}`.
- Si es pago: `/pagos/{id}`.
- Mantener el estilo de card alargada.
- Si hace falta, crear markup reutilizable para usarlo luego en grupo.

### Fuera de alcance

- No cambiar queries del feed salvo que falte el id o tipo necesarios.
- No cambiar calculo de saldos.

### Criterios de aceptacion

- Tap/click en un movimiento abre su detalle.
- No se rompe el feed.
- Los estados vacios siguen correctos.

### Commit sugerido

`ux: hace clickeables los movimientos del home`

## UX3 - Redisenio de pantalla de Grupo

### Objetivo

Limpiar la pantalla operativa del grupo y mover la configuracion del grupo a un lugar mas natural.

### Alcance

- En `app/Views/grupos/show.php`, reemplazar el bloque cargado de acciones por:
  - Header simple del grupo.
  - Saldo/resumen.
  - CTA principal para agregar gasto.
  - Boton secundario "Ver grupo" o "Configurar grupo".
- El boton "Ver grupo" debe llevar a la pantalla de edicion/configuracion del grupo existente o a una nueva vista si es estrictamente necesario.
- En configuracion del grupo deben quedar:
  - cambiar nombre;
  - cambiar descripcion;
  - cambiar estado;
  - agregar miembro;
  - quitar miembro;
  - cambiar rol.
- Quitar gestion de miembros de la pantalla operativa del grupo.
- Movimientos del grupo deben verse como cards alargadas, consistentes con Home.
- Cada movimiento del grupo debe ser clickeable al detalle.
- En mobile, agregar FAB contextual "Agregar gasto" dentro del grupo.

### Fuera de alcance

- No cambiar la logica de balance.
- No cambiar permisos backend salvo que un boton nuevo necesite usar permisos ya existentes.
- No redisenar balance completo.

### Criterios de aceptacion

- La pantalla de grupo se ve menos cargada.
- La gestion de miembros ya no ensucia la pantalla operativa.
- Admin puede gestionar grupo desde configuracion.
- Member no ve acciones que no puede ejecutar.
- Movimientos clickeables.
- FAB de grupo crea gasto para ese grupo.

### Commit sugerido

`ux: reorganiza pantalla de grupo`

## UX5 - Perfil: editar email

### Objetivo

Permitir que el usuario edite su propio email desde Perfil.

### Alcance

- Agregar formulario de email en perfil.
- Validar email requerido, formato valido y unicidad contra otros usuarios.
- Actualizar sesion (`userEmail`) despues del cambio.
- Mantener rol global como dato informativo, no editable.

### Fuera de alcance

- No cambiar login.
- No cambiar recuperacion de password.
- No cambiar ABM admin de usuarios.

### Criterios de aceptacion

- Usuario cambia email propio.
- No puede usar un email de otro usuario.
- Sigue logueado con el nuevo email.
- Validacion backend y CSRF correctos.

### Commit sugerido

`feat: permite editar email propio`

## UX6 - Medios de cobro simplificados

### Objetivo

Simplificar la pantalla y el formulario de medios de cobro para que se alineen al estilo actual de la app.

### Campos

- Nombre: obligatorio.
- Titular: obligatorio.
- CVU/CBU: opcional, pero requerido si no hay alias.
- Alias: opcional, pero requerido si no hay CVU/CBU.
- Banco: opcional.
- Link de pago: opcional.

### Alcance

- Quitar `tipo` de la UI.
- Conservar columna `tipo` si existe, sin hacer migraciones destructivas.
- Ajustar validaciones backend.
- Redisenar index y form con cards mobile-first.
- Mantener favorito/activo.
- El flujo de balance/pago asistido debe mostrar nombre, alias, CBU/CVU o link sin depender de `tipo`.

### Fuera de alcance

- No cambiar pagos.
- No integrar bancos reales.
- No cambiar medios favoritos salvo validacion existente.

### Criterios de aceptacion

- Se puede guardar con alias sin CBU/CVU.
- Se puede guardar con CBU/CVU sin alias.
- No se puede guardar sin alias y sin CBU/CVU.
- Titular obligatorio.
- Pantalla visualmente consistente.

### Commit sugerido

`ux: simplifica medios de cobro`

## UX7 - Balance: pagar solo lo propio

### Objetivo

Evitar que usuarios comunes registren pagos de deudas ajenas desde balance.

### Alcance

- En `app/Views/grupos/balance.php`, usuario comun ve CTA de pagar solo en sus propias deudas.
- Admin de grupo puede ver todas las deudas; si se mantiene CTA admin para terceros, el backend debe validarlo explicitamente.
- En controller de pagos, validar que el usuario logueado puede registrar el pago solicitado.
- Si no hay soporte claro para pagar en nombre de otro, entonces admin ve informacion completa pero el CTA de pagar tambien queda limitado al usuario autenticado.

### Fuera de alcance

- No cambiar calculo de deuda.
- No implementar flujo nuevo de "pagar en nombre de otro" si no existe una regla clara.

### Criterios de aceptacion

- Usuario comun no puede registrar pagos de otro.
- No aparecen botones que generen movimientos espurios.
- Backend bloquea intentos manuales.
- Admin conserva visibilidad del balance completo.

### Commit sugerido

`fix: limita pagos de balance al usuario correspondiente`

## UX4 - Unificar division de gastos

### Estado

Pendiente. Siguiente fase ejecutable.

### Rama sugerida

`feature/division-gastos-unificada`

### Objetivo

Reemplazar la mezcla actual de participantes, preview igualitario y division avanzada por un flujo unico que guarde exactamente lo que luego usa el balance.

### Alcance UI

- En alta de gasto:
  - descripcion;
  - monto con formato argentino en vivo (`1.999,87`);
  - fecha visible y editable, default hoy;
  - grupo implicito si se viene desde un grupo;
  - pagador editable con select de miembros;
  - categoria inferida en vivo por diccionario;
  - seccion division con select "como se divide".
- En edicion de gasto:
  - mantener la division guardada;
  - permitir cambiar pagador y modalidad si el usuario tiene permiso de edicion;
  - recalcular la division con las mismas reglas server-side.
- El bloque viejo de pagador/participantes igualitarios no debe quedar duplicado debajo de la nueva seccion de division.
- Si el gasto se crea desde un grupo, no mostrar selector de grupo; conservar `grupo_id` oculto para el POST.
- Si el gasto se crea desde una entrada global sin grupo, conservar el selector de grupo para no romper el flujo futuro de gastos sin contexto.
- Modos visibles:
  - igualitario;
  - porcentaje;
  - monto fijo.
- Segun modo:
  - Igualitario: muestra integrantes y monto calculado por persona.
  - Porcentaje: input de porcentaje por integrante, indicador de faltante/exceso hasta 100%.
  - Monto fijo: input de monto por integrante, indicador de diferencia contra total.
- La UI debe actualizarse al escribir, borrar, cambiar monto, cambiar modo o cambiar pagador.

### Alcance backend

- `create()` y `update()` deben validar server-side:
  - pagador pertenece al grupo;
  - integrantes pertenecen al grupo;
  - no hay integrantes extras ni faltantes respecto del grupo cuando el modo exige a todos;
  - total dividido coincide con monto del gasto;
  - porcentajes suman 100;
  - montos fijos suman el total;
  - no hay montos negativos, salvo que una regla explicita lo permita (esta fase no lo permite).
- No confiar en calculos JavaScript. La UI puede previsualizar, pero PHP recalcula y valida.
- Guardar `gasto_participantes.monto_asignado` con los valores reales.
- Guardar `division_tipo` para poder editar.
- Si `gasto_divisiones` existe y se decide escribir ahi tambien, debe ser espejo exacto de `gasto_participantes`. No usarlo como fuente paralela divergente.
- Fallback para gastos viejos: si no tienen datos completos de division, seguir mostrando/calculando de forma compatible.

### Fuera de alcance

- No implementar cuotas/partes ni ajuste.
- No subir recibos ni notas; eso ya pertenece a otra fase.
- No cambiar reportes salvo que dependan directamente de los montos asignados ya existentes.

### Criterios de aceptacion

- Alta y edicion preservan division.
- Balance refleja la division real.
- Tests cubren igualitario, porcentaje, monto fijo, redondeo y validaciones invalidas.
- No hay doble fuente de verdad.
- Al crear gasto desde grupo, al guardar vuelve al detalle del grupo.
- El boton principal de guardar queda visible fuera de cualquier desplegable de "Mas opciones".

### Commit sugerido

`feat: unifica division de gastos`

## Verificaciones obligatorias para UX4

- `git status --short --branch`
- `git log --oneline main..HEAD`
- `git diff --check main..HEAD`
- `php -l` en archivos PHP tocados
- `php vendor/bin/phpunit --no-coverage`
- `php spark routes`
- `php spark migrate:status` si MySQL esta corriendo
- Buscar mojibake en archivos tocados. Revisar secuencias tipicas de texto roto, simbolos de reemplazo y escapes Unicode visibles.

## Verificacion final antes del PR de UX4

- `git status --short --branch`
- `git log --oneline main..HEAD`
- `git diff --stat main..HEAD`
- `git diff --check main..HEAD`
- `php -l` en todos los PHP tocados
- `php vendor/bin/phpunit --no-coverage`
- `php spark routes`
- `php spark migrate:status` si MySQL esta corriendo
- Busqueda de mojibake en todos los archivos tocados
- Auditoria de alcance: listar controllers, models, views, migrations y assets tocados.
- Confirmar que no se toco produccion/deploy.

## Fuera de alcance global

Este roadmap no incluye:

- Produccion/deploy.
- Periodos.
- Registro publico.
- Integracion real con bancos o Mercado Pago.
- Nuevos reportes.
- Reescritura de frontend con React/Astro.
- Copiar marca o assets de Splitwise.

## Entrega esperada del agente

Al finalizar UX4:

- Rama actual: `feature/division-gastos-unificada`.
- Commits separados por capa.
- PR creado contra `main`.
- Informe con:
  - resumen de UX4;
  - archivos tocados;
  - decisiones tecnicas;
  - verificaciones;
  - riesgos residuales;
  - confirmacion de working tree limpio.
- No mergear.

## Prompt sugerido para ejecutar UX4

Usa splitwise y arranca la siguiente fase desde `roadmap-visuales.md`: UX4 - Unificar division de gastos.

Trabaja desde `main` actualizado y crea una rama nueva llamada `feature/division-gastos-unificada`. No uses la rama vieja `feature/visuales-refinamiento-integral`. No hagas merge sin autorizacion explicita.

Implementa solo UX4:

- Unificar el formulario de gasto para que la division se maneje desde una unica seccion.
- Hacer editable el pagador con un select de miembros del grupo, default usuario logueado.
- Ocultar el selector visible de grupo cuando el gasto viene desde un grupo, conservando `grupo_id` oculto.
- Quitar la duplicacion visual de pagador/participantes igualitarios fuera de la nueva seccion.
- Mantener fecha visible y editable, default hoy.
- Mantener descripcion y monto como carga rapida, con formato argentino en vivo.
- Categoria inferida en vivo desde descripcion como indicador, no como selector principal.
- Soportar modos visibles: igualitario, porcentaje y monto fijo.
- Validar server-side todo lo calculado: pagador pertenece al grupo, participantes pertenecen al grupo, sumas correctas, porcentajes 100, montos igual al total, sin negativos.
- Guardar `gasto_participantes.monto_asignado` como fuente que refleja la division real.
- Mantener `gastos.division_tipo`.
- Si se escribe tambien en `gasto_divisiones`, debe ser espejo exacto y no fuente divergente.
- Preservar fallback para gastos viejos.
- Al guardar un gasto creado desde un grupo, volver al detalle del grupo.
- Mantener el boton principal de guardar fuera de cualquier desplegable.

Fuera de alcance:

- No implementar cuotas/partes ni ajuste.
- No tocar reportes, medios de cobro, perfil, navegacion, recibos, produccion/deploy ni integraciones externas.
- No hacer cambios destructivos de migraciones o tablas.

Verifica:

- `git status --short --branch`
- `git log --oneline main..HEAD`
- `git diff --check main..HEAD`
- `php -l` en PHP tocados
- `php vendor/bin/phpunit --no-coverage`
- `php spark routes`
- `php spark migrate:status` si MySQL corre
- busqueda de mojibake en archivos tocados

Al final crea un PR contra `main`, dejalo sin mergear y pasame informe con commits, archivos tocados, decisiones tecnicas, tests, riesgos y confirmacion de working tree limpio.
