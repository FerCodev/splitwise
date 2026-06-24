# Roadmap activo - SplitWise

Este es el unico roadmap operativo del proyecto.

Reglas para agentes:

- Usar este archivo como unica fuente para decidir la siguiente fase.
- Ignorar `Roadmaps historico/` y cualquier roadmap viejo, salvo que el usuario lo pida explicitamente.
- Ejecutar siempre la primera fase numerada que no este marcada como `COMPLETADA`.
- No proponer focos genericos si existe una fase pendiente en este archivo.
- Cada fase debe salir desde `main` actualizado, en su propia rama.
- Crear PR al final de cada fase.
- No mergear sin autorizacion explicita del usuario.
- No implementar tareas de produccion/deploy en este ciclo.

Siguiente fase pendiente: (ninguna - todas las fases del roadmap estan completadas).

## Dashboard - Resumen financiero y filtros

Completada como `feature/dashboard-mejoras`, mergeada PR #23, commit `9773a3e`.

Cambios:
- Cards de resumen con iconos, colores, enlaces y metricas (grupos activos, saldo total, debe, te deben).
- Seccion "Deudas pendientes" con lista de hasta 6 items, monto, grupo y enlace a balance.
- Feed de actividad reciente con filtros de grupo y periodo (JS local).
- Performance: balance calculado una sola vez por grupo.
- Sin cambios en backend, rutas, migraciones, permisos ni calculo contable.

## Fase 1 - Flujo de gasto simple

Completada como `feature/ux-flujo-gasto-simple`, mergeada PR #24, commit `ae235a1`.

Cambios:
- Fecha visible en formulario principal (fuera de "Mas acciones").
- Pagador select visible en el formulario, no oculto en modal.
- Presets de division que cambian pagador ocultos cuando el pagador esta bloqueado (edicion member).
- Normalizador backend de monto argentino con fallback desde monto_visual.
- Categoria inferida desde descripcion (sin selector en flujo principal).
- Mas acciones solo contiene nota y comprobante.
- Sin cambios en schema, migraciones, rutas ni permisos.

## Estado actual

La app ya tiene base funcional estable:

- Autenticacion, sesiones y recuperacion de contrasena.
- Dashboard y navegacion mobile.
- Grupos, miembros, roles globales y permisos de grupo.
- Gastos, pagos, categorias, balance y transferencias sugeridas.
- Medios de cobro y pagos asistidos.
- Division de gastos con modos base.
- Reportes y exportaciones.
- Nota y recibo en gasto.
- Limpieza tecnica previa completada.

Este roadmap se enfoca en mejorar experiencia de uso y claridad visual, no en despliegue.

## Fase 1 - Flujo de gasto simple

Estado: COMPLETADA (PR #24, commit ae235a1)

Rama: `feature/ux-flujo-gasto-simple`

Objetivo:

Hacer que crear o editar un gasto sea rapido, claro y parecido a una carga mobile simple.

Alcance:

- Simplificar la pantalla de gasto para que los campos principales sean:
  - descripcion
  - monto
  - fecha
- El monto debe mostrarse y aceptarse con formato argentino:
  - miles con punto
  - decimales con coma
  - ejemplo: `1.999,87`
- La fecha debe venir por defecto con la fecha del dia y seguir siendo editable.
- Si el gasto se crea desde un grupo:
  - el grupo debe quedar implicito
  - conservar `grupo_id` como hidden input
  - no mostrar selector ni bloque visual de grupo
  - al guardar, volver al grupo de origen
- Si el gasto se crea desde una pantalla global:
  - mantener la posibilidad de elegir grupo
  - no eliminar soporte futuro para gastos sin grupo
- El pagador debe ser editable:
  - select con miembros del grupo
  - default: usuario logueado
  - permite registrar un gasto pagado por otro miembro
  - validar backend que el pagador elegido pertenece al grupo
- La categoria debe inferirse desde la descripcion:
  - usar diccionario simple de palabras clave
  - actualizar mientras el usuario escribe
  - mostrar indicador visible de categoria asignada
  - no mostrar selector de categoria en el flujo principal
  - mantener fallback seguro si no se puede inferir
- El boton Guardar debe estar siempre visible.
- El boton Guardar no debe quedar dentro de "Mas opciones".
- "Mas opciones" puede contener campos secundarios, pero no los datos obligatorios para guardar.
- La division debe integrarse de forma clara en el formulario:
  - selector "Quien pago"
  - selector "Como se divide"
  - soportar solo modos reales actuales:
    - igualitario
    - monto fijo
    - porcentaje
  - mostrar participantes y preview segun modo
  - actualizar preview al escribir o borrar valores
  - alertar si porcentaje no suma 100
  - alertar si monto fijo no suma el total

Fuera de alcance:

- No implementar cuotas.
- No implementar partes.
- No implementar ajuste.
- No cambiar schema.
- No cambiar calculos de balance salvo que sea necesario para preservar comportamiento existente.
- No tocar reportes.
- No tocar produccion/deploy.

Criterios de aceptacion:

- Crear gasto desde un grupo vuelve al grupo.
- Crear gasto desde pantalla global sigue funcionando.
- Guardar siempre esta visible.
- El pagador puede elegirse entre miembros del grupo.
- Categoria se asigna automaticamente por descripcion y se ve en pantalla.
- Monto usa formato argentino sin romper el valor guardado.
- Division igualitaria, monto fijo y porcentaje funcionan sin duplicar controles viejos.
- Tests existentes pasan.
- No hay mojibake.

Verificaciones obligatorias:

- `git status --short`
- `git log --oneline main..HEAD`
- `git diff --check main..HEAD`
- `php -l` en archivos PHP tocados
- `php vendor/bin/phpunit --no-coverage`
- `php spark routes`
- `php spark migrate:status` si MySQL corre
- busqueda de mojibake en archivos tocados

Informe final requerido:

- Rama actual
- Commits
- Archivos modificados
- Cambios de UX realizados
- Validaciones backend agregadas o preservadas
- Tests/verificaciones
- Riesgos residuales
- PR creado
- No mergear

### Fase 1 - Plan operativo para agente de desarrollo

Este bloque convierte la Fase 1 en una ejecucion por subfases. El agente debe seguirlo en orden y no avanzar a otra fase del roadmap aunque encuentre mejoras relacionadas.

#### Prompt base para iniciar el agente

Objetivo del agente:

Implementar `Fase 1 - Flujo de gasto simple` del archivo `roadmap-activo.md` en la rama `feature/ux-flujo-gasto-simple`, partiendo de `main` actualizado, sin tocar deploy/produccion, sin cambiar schema y sin implementar funcionalidades diferidas.

Reglas de trabajo:

- Usar `roadmap-activo.md` como unica fuente de alcance.
- Ignorar `Roadmaps historico/` salvo pedido explicito.
- Crear o usar solamente la rama `feature/ux-flujo-gasto-simple`.
- Hacer commits chicos y descriptivos por subfase.
- No mergear.
- Crear PR al final.
- Si una verificacion no puede correrse por entorno local, reportar causa exacta y evidencia.
- Mantener encoding UTF-8; no introducir mojibake.
- Preservar patrones actuales de CodeIgniter 4, Bootstrap 5.3 y vistas PHP.

#### Archivos probables a revisar/tocar

- `app/Controllers/Gastos.php`
- `app/Models/Gasto.php`
- `app/Models/GastoDivision.php`
- `app/Models/Categoria.php`
- `app/Models/Grupo.php`
- `app/Views/gastos/form.php`
- `app/Views/gastos/show.php` solo si hace falta reflejar datos del nuevo flujo.
- `app/Views/grupos/show.php` solo si hace falta corregir link de "agregar gasto" o retorno.
- `tests/unit/DivisionGastosTest.php`
- `tests/unit/CategoriaGastoTest.php`
- `tests/unit/FlujoGruposGastosTest.php`
- Nuevos tests unitarios si conviene aislar parsing de monto, inferencia de categoria o validacion de pagador.

No tocar:

- Migraciones.
- Reportes.
- Pagos.
- Produccion/deploy.
- `Roadmaps historico/`.

#### Subfase 0 - Preparacion y baseline

Objetivo:

Entrar a la rama correcta con una foto clara del estado inicial.

Pasos:

- Verificar `git status --short`.
- Actualizar `main` segun el flujo habitual del repo.
- Crear/switch a `feature/ux-flujo-gasto-simple`.
- Leer `app/Controllers/Gastos.php`, `app/Views/gastos/form.php`, `app/Models/Gasto.php`, `app/Models/GastoDivision.php`, `app/Models/Categoria.php`, `app/Models/Grupo.php` y tests relacionados.
- Ejecutar baseline si el entorno lo permite:
  - `php vendor/bin/phpunit --no-coverage`
  - `php spark routes`

Criterio de salida:

- Rama lista.
- Riesgos iniciales anotados.
- Se sabe si tests/rutas pasan antes de tocar codigo.

#### Subfase 1 - Contrato backend del formulario

Objetivo:

Asegurar que el backend acepte el nuevo flujo sin debilitar permisos ni consistencia contable.

Especificaciones:

- Mantener `grupo_id` requerido para esta fase. El soporte futuro para gastos sin grupo no debe eliminarse conceptualmente, pero no se implementa ahora.
- Si el gasto viene de un grupo, validar que el usuario logueado pertenece al grupo y que el estado permite `gasto_create` o `gasto_edit`.
- `pagador_id` debe poder elegirse entre miembros del grupo al crear y editar.
- Validar siempre que `pagador_id` pertenece al grupo.
- En edicion, no romper reglas actuales de permisos:
  - admin de grupo puede editar gasto ajeno.
  - member solo segun reglas vigentes de `GroupPermission`.
- Si se decide conservar la restriccion existente que impide a member cambiar pagador en edicion, documentarlo como riesgo/residuo porque el alcance pide pagador editable.
- Normalizar monto antes de validar:
  - aceptar entrada visual argentina `1.999,87`.
  - guardar decimal compatible con DB, ejemplo `1999.87`.
  - rechazar valores vacios, cero o negativos.
- Preservar escritura sincronizada en:
  - `gastos`
  - `gasto_participantes`
  - `gasto_divisiones`
- Limitar `division_tipo` a:
  - `igualitario`
  - `monto_fijo`
  - `porcentaje`
- Mantener validaciones backend:
  - porcentaje suma 100 con tolerancia actual.
  - monto fijo suma total con tolerancia actual.
  - participantes pertenecen al grupo.
  - al menos un participante.
- Categoria:
  - backend debe aceptar `categoria_id` inferida desde la vista.
  - si falta, no existe o no esta activa, usar `Otros`.
  - no confiar solo en JavaScript para una categoria valida.

Criterio de salida:

- `create()` y `update()` soportan el nuevo contrato.
- No hay cambio de schema.
- Tests unitarios de calculo de division siguen pasando o se actualizan si habia cobertura incompleta.

#### Subfase 2 - UX principal del gasto simple

Objetivo:

Convertir `app/Views/gastos/form.php` en una carga rapida y mobile-first.

Especificaciones de pantalla:

- Primera vista del formulario debe priorizar:
  - descripcion
  - monto
  - fecha
  - quien pago
  - como se divide
  - participantes/preview
  - guardar
- La fecha debe estar visible, no dentro de "Mas opciones".
- Fecha default: `date('Y-m-d')` para alta, valor existente para edicion, `old('fecha')` si hay error.
- El boton Guardar debe estar siempre visible:
  - no dentro de collapse.
  - idealmente en barra inferior sticky en mobile o bloque fijo visible al final del viewport/formulario.
  - debe convivir con Cancelar sin tap targets chicos.
- "Mas opciones" solo puede contener secundarios:
  - nota
  - recibo/comprobante
  - otros campos no obligatorios.
- No duplicar controles viejos de division.
- Evitar texto explicativo largo dentro de la UI; usar labels claros y estados visibles.

Grupo:

- Si existe `grupoId`:
  - renderizar `<input type="hidden" name="grupo_id" ...>`.
  - no mostrar selector ni bloque visual de grupo.
  - Cancelar debe volver a `/grupos/{grupoId}`.
  - Al guardar, backend ya debe volver a `/grupos/{grupoId}`.
- Si no existe `grupoId`:
  - mostrar selector de grupo.
  - mantener flujo actual de seleccion y carga de miembros.
  - no bloquear soporte futuro para gasto sin grupo con decisiones irreversibles.

Pagador:

- Mostrar selector visible "Quien pago".
- Opciones: miembros del grupo.
- Default: usuario logueado si pertenece al grupo.
- En edicion: default valor existente del gasto, salvo `old('pagador_id')`.

Criterio de salida:

- Crear desde grupo se siente como formulario corto.
- Crear desde pantalla global sigue permitiendo seleccionar grupo.
- Guardar no queda oculto ni dentro de "Mas opciones".

#### Subfase 3 - Monto argentino robusto

Objetivo:

Que el input sea comodo para Argentina sin romper el valor persistido.

Especificaciones:

- Input visible:
  - acepta `1.999,87`.
  - usa punto para miles y coma para decimales.
  - permite escribir y borrar sin dejar el formulario trabado.
  - inputmode recomendado: `decimal`.
- Hidden real:
  - enviar `monto` normalizado como `1999.87`.
  - si el input queda vacio, hidden vacio para que valide backend.
- Al cargar edicion:
  - mostrar `number_format($monto, 2, ',', '.')`.
- Al validar con error:
  - preservar `old('monto_visual')` y `old('monto')`.
- Backend:
  - agregar helper/metodo privado para normalizar monto argentino antes de validar, o adaptar el request antes de reglas.
  - no depender solo del hidden generado por JS.

Casos manuales minimos:

- `1999,87` guarda `1999.87`.
- `1.999,87` guarda `1999.87`.
- `1.999` guarda `1999.00` o el comportamiento elegido, documentado.
- `1.999,` no debe romper preview ni backend.
- borrar todo muestra validacion, no `0.00` silencioso.

Criterio de salida:

- El valor mostrado y el valor guardado son consistentes.
- La division recalcula con el valor normalizado.

#### Subfase 4 - Categoria inferida por descripcion

Objetivo:

Eliminar el selector de categoria del flujo principal y mostrar una asignacion automatica comprensible.

Especificaciones:

- Mantener `categoria_id` como hidden.
- Usar diccionario simple de palabras clave en JavaScript.
- El diccionario debe mapear a nombres de categorias existentes; construir `valueMap` desde `$categorias`.
- Actualizar mientras el usuario escribe.
- Mostrar indicador visible, por ejemplo badge/texto corto:
  - `Categoria: Comida`
  - fallback visible: `Categoria: Otros` si no hay match.
- No mostrar selector de categoria en el flujo principal.
- Si no se encuentra categoria activa para el match, usar `Otros`.
- En edicion:
  - mostrar la categoria actual al abrir.
  - si el usuario cambia descripcion, recalcular.
- Backend debe seguir protegiendo fallback a `Otros`.

Diccionario inicial sugerido:

- Supermercado: `super`, `supermercado`, `mercado`, `verduleria`, `almacen`, `compras`
- Comida: `restaurant`, `restaurante`, `comida`, `cena`, `almuerzo`, `delivery`, `pizza`, `cafeteria`
- Transporte: `uber`, `taxi`, `colectivo`, `subte`, `tren`, `viaje`
- Combustible: `nafta`, `combustible`, `gasolina`, `estacionamiento`
- Farmacia: `farmacia`, `remedio`, `medicamento`
- Servicios: `luz`, `agua`, `gas`, `internet`, `telefono`
- Vivienda: `alquiler`, `expensas`
- Entretenimiento: `cine`, `netflix`, `streaming`, `salida`

Criterio de salida:

- Categoria se ve siempre.
- Nunca queda un `categoria_id` invalido que rompa guardado.

#### Subfase 5 - Division integrada en el formulario

Objetivo:

Reemplazar el modal/presets si ensucia el flujo, o reordenarlo si se conserva, para que la division quede clara dentro del formulario principal.

Especificaciones:

- Mostrar en el formulario:
  - selector "Quien pago".
  - selector "Como se divide".
  - lista de participantes.
  - preview por participante.
- `Como se divide` solo debe ofrecer:
  - igualitario
  - monto fijo
  - porcentaje
- Para `igualitario`:
  - ocultar inputs de valor.
  - mostrar monto calculado por participante.
- Para `monto_fijo`:
  - mostrar input por participante.
  - recalcular suma al escribir.
  - alertar si suma distinto al total.
- Para `porcentaje`:
  - mostrar input por participante.
  - recalcular monto estimado.
  - alertar si suma distinto de 100.
- Al tildar/destildar participantes, regenerar preview y hidden inputs.
- Hidden inputs esperados:
  - `participantes[]`
  - `division_tipo`
  - `division_valores[n][user_id]`
  - `division_valores[n][valor]`
- No generar `division_valores` para participantes no seleccionados.
- La UI debe funcionar en mobile sin desbordes horizontales.
- Si se mantiene un collapse, no debe esconder datos obligatorios para guardar.

Criterio de salida:

- No quedan controles duplicados de division.
- Preview y validaciones visuales coinciden con validaciones backend.

#### Subfase 6 - Retornos y flujo global/grupo

Objetivo:

Pulir navegacion para que alta/edicion no saque al usuario de contexto.

Especificaciones:

- Alta desde `/gastos/nuevo?grupo_id={id}`:
  - no muestra selector de grupo.
  - guarda y vuelve a `/grupos/{id}`.
  - cancelar vuelve a `/grupos/{id}`.
- Alta desde `/gastos/nuevo`:
  - muestra selector de grupo.
  - al elegir grupo, carga miembros.
  - guardar vuelve al grupo elegido, salvo que el flujo existente tenga una razon clara para volver a gastos.
- Edicion:
  - conservar grupo original.
  - guardar vuelve a `/grupos/{grupoIdOriginal}`.
  - cancelar vuelve al grupo original o al detalle del gasto si el flujo actual lo requiere; elegir uno y documentarlo.

Criterio de salida:

- Los criterios de aceptacion de retorno estan cubiertos manualmente.

#### Subfase 7 - Tests y verificaciones

Objetivo:

Cerrar la fase con confianza tecnica y reporte completo.

Tests recomendados:

- Agregar o ajustar tests puros para:
  - normalizacion de monto argentino si se extrae a metodo testeable.
  - `Gasto::calcularMontosDivision` para igualitario, monto fijo y porcentaje.
  - fallback de categoria a `Otros`.
  - pagador elegido debe pertenecer al grupo, aunque sea con test de regla aislada si no hay DB.
- No agregar tests fragiles que dependan de navegador si el proyecto no tiene stack E2E.

Verificaciones obligatorias antes del PR:

- `git status --short`
- `git log --oneline main..HEAD`
- `git diff --check main..HEAD`
- `php -l` en cada PHP tocado.
- `php vendor/bin/phpunit --no-coverage`
- `php spark routes`
- `php spark migrate:status` si MySQL corre.
- Buscar mojibake en archivos tocados con patrones:
  - `Ã`
  - `Â`
  - `â`

Criterio de salida:

- PR creado.
- Informe final completo segun la seccion "Informe final requerido".

#### Definicion de listo para PR

La fase esta lista para PR solo si:

- Todos los criterios de aceptacion de Fase 1 estan cubiertos.
- No se tocaron reportes, deploy, schema ni fases futuras.
- El formulario de gasto desde grupo y desde pantalla global funciona.
- El pagador elegido se valida en backend.
- Monto argentino funciona en UI y backend.
- Categoria inferida tiene fallback seguro.
- Division igualitaria, monto fijo y porcentaje guardan datos coherentes en `gastos`, `gasto_participantes` y `gasto_divisiones`.
- Se ejecutaron las verificaciones obligatorias o se documento por que alguna no pudo ejecutarse.

## Fase 2 - Pantalla de grupo operativa

Estado: COMPLETADA (PR #25, commit 722eda4)

Rama: `feature/ux-grupo-operativo`

Objetivo:

Ordenar la pantalla del grupo para que el usuario vea primero saldo, movimientos y accion principal.

Nota de orden:

- Esta fase debe ejecutarse recien cuando la Fase 1 este completada, con PR creado y sin mergear salvo autorizacion del usuario.
- Si un agente esta trabajando en Fase 1, no debe incorporar estos cambios en esa rama aunque afecten pantallas relacionadas.

Alcance:

- Reducir carga visual del header del grupo.
- En la card/header principal del grupo:
  - reemplazar el boton ancho unico `Configurar grupo` por dos acciones en la misma fila.
  - boton izquierdo: `Ver balance`, enlaza a `/grupos/{id}/balance`.
  - boton derecho: `Configurar grupo` o `Configurar` con icono de engranaje si el texto no entra bien en mobile.
  - ambos botones deben ocupar ancho equivalente, con separacion chica y sin overflow.
  - mantener colores consistentes: balance como outline/secundario, configurar como accion primaria.
- Reemplazar bloques de multiples botones por acciones secundarias discretas cuando corresponda.
- Acciones secundarias disponibles sin ensuciar la vista principal:
  - editar grupo/configurar grupo
  - eliminar grupo
  - cambiar estado
  - registrar pago
- Boton principal visible: agregar gasto.
- Agregar FAB mobile en pantalla de grupo:
  - texto: `Agregar gasto`
  - accion: nuevo gasto dentro del grupo actual
- Mover gestion de miembros fuera del flujo principal de movimientos.
- La gestion de grupo debe permitir:
  - cambiar nombre
  - cambiar descripcion
  - cambiar estado
  - agregar miembros
  - quitar miembros
  - cambiar rol de miembros
- Movimientos del grupo:
  - cards alargadas similares a home
  - click lleva al detalle del gasto o pago
  - menos ruido visual
  - mantener descripcion, fecha, participantes/pagador, monto y badge de categoria si aplica
  - hacer las cards ligeramente mas altas/comodas en mobile, con mas padding vertical o altura minima
  - evitar solapamientos entre monto, descripcion y nombres largos
  - corregir textos de pagos que renderizan entidades HTML literales, por ejemplo `pag&oacute;` o `pag&amp;oacute;`.
  - los movimientos de pago deben verse como `Antonella pagó a Fernando`, no como `Antonella pag&oacute; a Fernando`.
  - mantener `esc()` para datos de usuario; no resolver con `html_entity_decode()` general sobre nombres/descripciones.
- Seccion Movimientos:
  - quitar los botones `Gastos` y `Pagos` que aparecen junto al titulo.
  - no eliminar rutas ni navegacion global; solo sacar esos botones de esta pantalla.
  - dejar el titulo `Movimientos` limpio y enfocado en el listado.
- Quitar fecha de creacion del header principal si no aporta.

Fuera de alcance:

- No cambiar calculo de balance.
- No cambiar permisos backend.
- No implementar invitaciones por link.
- No tocar reportes.
- No tocar produccion/deploy.

Criterios de aceptacion:

- Pantalla de grupo queda mas limpia.
- Acciones secundarias estan disponibles pero no ensucian la vista principal.
- La card/header del grupo muestra dos acciones principales: `Ver balance` y `Configurar grupo`/`Configurar`.
- Ya no se ven botones `Gastos` y `Pagos` al lado de `Movimientos`.
- Movimientos son clickeables.
- Cards de movimientos se ven un poco mas altas y comodas en mobile.
- Los textos de pagos no muestran entidades HTML literales (`&oacute;`, `&aacute;`, etc.).
- FAB agrega gasto en el grupo actual.
- Gestion de miembros queda en zona de configuracion o gestion del grupo.
- No hay overflow horizontal ni solapamientos en mobile.
- Desktop no queda roto por el ajuste mobile.
- Tests pasan.

## Fase 3 - Balance y pagos claros

Estado: COMPLETADA (PR #27, commit efdce7a)

Rama: `feature/ux-balance-pagos-claros`

Objetivo:

Evitar que un usuario registre pagos por deudas que no le corresponden.

Alcance:

- Usuario comun ve accion de pagar solo sobre sus propias deudas.
- No mostrar boton pagar junto a deudas de otros usuarios para usuarios comunes.
- Admin puede ver el balance completo.
- Admin no debe registrar pago "como otro usuario" salvo que se implemente explicitamente una accion separada.
- Revisar que el pago generado use siempre el usuario autenticado como origen, salvo flujo admin futuro.
- Mejorar textos para explicar que se esta registrando un pago manual.

Fuera de alcance:

- No integrar pagos reales.
- No transferir dinero desde la app.
- No implementar pago en nombre de otro usuario.
- No cambiar formula contable.

Criterios de aceptacion:

- Usuario comun no puede crear movimientos inconsistentes desde balance.
- Balance sigue mostrando informacion suficiente.
- Admin conserva visibilidad.
- Tests pasan.

## Fase 4 - Perfil y medios de cobro

Estado: COMPLETADA (PR #28, commit c7648de)

Rama: `feature/ux-perfil-medios-cobro`

Objetivo:

Completar autogestion de usuario y simplificar medios de cobro.

Alcance:

- Perfil:
  - editar nombre
  - editar email propio
  - validar unicidad de email
  - no permitir cambiar rol global desde perfil
- Medios de cobro:
  - redisenar visualmente con estilo actual de la app
  - simplificar formulario
  - campos:
    - nombre obligatorio
    - titular obligatorio
    - CVU/CBU opcional
    - alias opcional
    - banco opcional
    - link de pago opcional
  - debe requerir al menos alias o CVU/CBU
  - conservar favorito
  - conservar activo/inactivo

Fuera de alcance:

- No implementar KYC.
- No validar CBU/CVU contra entidades externas.
- No cambiar pagos asistidos salvo que sea necesario por nombres de campo.

Criterios de aceptacion:

- Usuario puede editar su email.
- Medios de cobro quedan mas simples.
- La validacion alias/CVU funciona.
- Tests pasan.

## Fase 5 - Pulido final mobile

Estado: COMPLETADA (PR #29, commit 339cdc2)

Rama: `feature/ux-pulido-mobile`

Objetivo:

Unificar detalles visuales y de navegacion despues de las fases anteriores.

Alcance:

- Revisar bottom nav:
  - Home
  - Grupos
  - Gastos
  - Reportes
  - Perfil
- Mover opciones secundarias al menu hamburguesa:
  - categorias
  - usuarios
  - medios de cobro
  - configuraciones
- Corregir menu hamburguesa mobile:
  - el boton hamburguesa superior debe abrir un menu completo, no solo `Mis medios de cobro` y `Resumen`.
  - incluir usuario/email, Mi perfil, Grupos, Gastos, Pagos, Reportes, Mis medios de cobro y Cerrar sesion.
  - si el usuario es admin, incluir seccion Administracion con Categorias y Usuarios.
  - mostrar `Resumen` solo cuando tenga sentido en dashboard o donde exista `resumenCollapse`.
  - eliminar offcanvas mobile duplicados o sin disparador para evitar que el agente mantenga dos menus divergentes.
- Quitar icono/flama de CodeIgniter Debug Toolbar de la UI mobile:
  - no debe aparecer el simbolo de CodeIgniter abajo a la derecha.
  - preferir deshabilitar la toolbar desde la configuracion/filtros de CodeIgniter para la app local.
  - no ocultarlo solo con CSS si el filtro sigue inyectando markup.
- Revisar cards para que no se pierdan con fondo blanco.
- Ajustar contraste, bordes y sombras.
- Revisar estados vacios.
- Revisar textos de botones.
- Revisar que los accesos principales sean coherentes en mobile.

Fuera de alcance:

- No cambiar logica contable.
- No cambiar schema.
- No tocar produccion/deploy.

Criterios de aceptacion:

- Navegacion mobile mas clara.
- Menu hamburguesa mobile muestra todas las opciones esperadas y no queda reducido a dos entradas.
- No aparece el icono/flama de CodeIgniter Debug Toolbar en pantallas mobile.
- Cards distinguibles del fondo.
- Acciones secundarias no aparecen como botones sueltos en home.
- Tests pasan.

## Fases explicitamente diferidas

No implementar todavia:

- Deploy / produccion.
- HTTPS obligatorio.
- Registro publico.
- Invitaciones por link.
- Pagos reales integrados.
- Division por cuotas.
- Division por partes.
- Division por ajuste.
- Gastos sin grupo.
