Roadmap pendiente SplitWise

Repositorio correcto:
C:\xampp\htdocs\SplitWise

No usar:
F:\Fernando\fer\proyectos\SplitWise

Nota:
Este archivo es documentacion local. Esta ignorado por Git y no debe subirse a GitHub.


ESTADO ACTUAL

Main ya incluye:

- Autenticacion con login/logout y sesiones.
- Dashboard financiero.
- ABM basico de usuarios.
- Roles globales admin/user.
- Grupos con miembros y roles por grupo.
- Permisos por grupo centralizados.
- Gestion de gastos con participantes.
- Gestion de pagos entre participantes.
- Medios de cobro del usuario.
- Pagos asistidos con copia de alias/CBU/CVU y link externo.
- Balance de grupo.
- Transferencias sugeridas.
- Estados de grupo: activo, cerrado, liquidado.
- ABM de categorias.
- Categorias de gastos administrables desde UI.
- Gastos relacionados con categorias por categoria_id.
- FK gastos.categoria_id -> categorias.id con DELETE RESTRICT.
- Filtros y visualizacion por categoria.
- Reportes y estadisticas con exportacion CSV.
- Auditoria UX inicial y simplificacion visual.
- Documentacion navegable en /doc/.
- Redisenio operativo de pantallas.
- Confirmaciones destructivas con modales Bootstrap.
- Recuperacion de contrasena con token seguro.
- Emails transaccionales SMTP para recuperacion y aviso de cambio de contrasena.
- Preview de division igualitaria al cargar gastos.
- Mejoras mobile finas en dashboard/categorias.
- CSRF global activo.
- Manejo de errores 400/403/404 mas amigable.
- CSRF invalido/vencido redirige con mensaje claro.
- AuthFilter informa sesion vencida al redirigir al login.
- README y tests base saneados.
- Limpieza de ramas locales/remotas mergeadas.
- Bugfix rutas de gestión de miembros resuelto.
- Seguridad hardening: encryption key documentada, contraseñas mínimo 8 caracteres, regeneración de sesión activada, locale y timezone Argentina.
- Mojibake corregido en env template.
- Autogestión de usuario: perfil propio, editar nombre, cambio de contraseña desde sesión.
- Paginación en listados de gastos, pagos, usuarios y categorías.
- Base visual mobile-first y CSS propio en public/assets/app.css.

Ultima fase mergeada:
Fase 26 - Base visual mobile-first y CSS propio

Fase actual:
Redisenio mobile-first

Estado de fase actual:
Fase 26 mergeada.
La base CSS ya esta separada en public/assets/app.css.
Las proximas fases visuales se trabajaran con una rama integradora de redisenio:
feature/mobile-redesign.
Main sincronizado con origin/main.
Working tree limpio.
Listo para continuar el redisenio mobile desde la rama integradora feature/mobile-redesign.

Rama descartada:
feature/periodos

Motivo:
Los periodos agregan complejidad al nucleo de gastos, pagos y balance, pero no encajan con la vision actual de producto: una app simple basada en grupos, gastos, pagos y balance. No mergear esta rama a main.

Estado de categorias actual:

- Existe tabla categorias.
- La tabla gastos usa categoria_id.
- La columna textual gastos.categoria fue eliminada.
- "Otros" es categoria protegida.
- Las categorias pueden activarse/desactivarse.
- Las categorias usadas por gastos no se eliminan.
- La FK usa DELETE RESTRICT para proteger gastos.
- Categorias actuales:
  - Supermercado
  - Servicios
  - Combustible
  - Farmacia
  - Mascotas
  - Transporte
  - Comida
  - Viajes
  - Otros


PRINCIPIO UX TRANSVERSAL

La app debe sentirse como una herramienta simple de uso diario, no como un panel administrativo pesado.

Criterios generales para pantallas:

- Arriba: navbar simple con identidad de la app y accesos esenciales.
- Luego: acciones principales de la pantalla, visibles y faciles de usar.
- Luego: contenido principal ordenado por relevancia o actividad reciente.
- Las metricas/resumenes deben apoyar la decision del usuario, no dominar la pantalla.
- En mobile, priorizar cards/listas escaneables antes que tablas anchas.
- Cada card/listado debe dejar claro:
  - que es.
  - estado actual.
  - ultimo movimiento o dato mas relevante.
  - accion principal siguiente.
- Los elementos mas recientes o con interaccion reciente deben aparecer primero.
- Mantener una jerarquia visual consistente en dashboard, grupos, gastos, pagos, medios de cobro, categorias, usuarios y reportes.

Concepto para pantalla principal:

- Navbar con nombre de la app.
- Bloque de acciones principales, por ejemplo "+ Nuevo grupo".
- Lista de grupos como contenido principal.
- Grupos ordenados por ultima interaccion:
  - ultimo gasto.
  - ultimo pago.
  - ultima actualizacion relevante.
- Grupos activos arriba.
- Grupos cerrados/liquidados o inactivos abajo.
- Cada grupo debe mostrar estado, ultimo movimiento y saldo/resumen util.


CRITERIOS GENERALES DE TRABAJO

Antes de tocar archivos:

- Estar en main actualizado.
- Crear una rama nueva desde main.
- Trabajar solamente en esa rama.
- No usar el repo viejo.
- No hacer merge sin autorizacion explicita.
- No mezclar fases grandes en una sola rama.
- Commits por tarea o grupo logico.
- Al terminar, dejar working tree limpio.

Excepcion para el redisenio mobile-first:

- El redisenio visual mobile se trabajara como una linea paralela con una rama integradora:
  feature/mobile-redesign.
- Esa rama integradora debe nacer desde main actualizado despues de Fase 26.
- Cada subfase visual nueva debe salir como subrama desde feature/mobile-redesign.
- Las subramas se mergean contra feature/mobile-redesign, nunca contra main.
- No hace falta abrir PR por cada subrama, salvo que se pida explicitamente.
- Al cerrar cada subfase mobile, auditar, mergear la subrama a feature/mobile-redesign con autorizacion, pushear feature/mobile-redesign como backup y actualizar este roadmap.
- El informe de cada subfase debe decir: "Subfase N mergeada en feature/mobile-redesign; rama integradora queda abierta para la siguiente subfase".
- No mergear feature/mobile-redesign a main hasta que el bloque visual este suficientemente estable y validado.
- Si durante el redisenio aparece un bug funcional, crear una rama corta desde main, resolverla, mergearla a main y luego actualizar feature/mobile-redesign con main.
- No meter cambios de backend, permisos, balance, migraciones ni logica contable en feature/mobile-redesign salvo autorizacion explicita.

Verificaciones esperadas al cerrar una fase:

- git status --short
- git log --oneline main..HEAD
- git diff --check main..HEAD
- php vendor/bin/phpunit --no-coverage
- php spark routes
- php spark migrate:status
- php -l en archivos PHP tocados
- Buscar mojibake o caracteres mal renderizados.


FASE 6 - Estabilizacion y manejo de errores - COMPLETADA

Rama:
feature/estabilizacion-errores

Objetivo:
Mejorar la experiencia ante errores comunes para que el usuario no vea pantallas tecnicas de CodeIgniter.

Motivo:
Despues de activar CSRF global y seguir agregando features, algunos errores esperables pueden aparecer como stacktraces en development. Conviene capturarlos mejor antes de seguir creciendo.

Alcance:

- Manejar CSRF invalido o vencido con mensaje claro.
- Manejar formularios viejos o token expirado.
- Manejar sesion expirada.
- Manejar acciones no permitidas.
- Revisar errores 403 y 404 visibles.
- Redirigir al login o a la pantalla anterior segun corresponda.
- Mostrar flash messages simples:
  - "La sesion o el formulario vencio. Volve a intentarlo."
  - "No tenes permiso para realizar esta accion."
- Evitar que el usuario final vea stacktraces en flujos normales.

Decisiones a revisar:

- Config\Security::$redirect.
- Comportamiento del filtro CSRF en development.
- Vista custom para error_403 si aporta valor.
- Mantener stacktrace tecnico solo cuando realmente haga falta durante desarrollo.

Criterios de aceptacion:

- Enviar un formulario con token vencido no muestra pantalla tecnica.
- El usuario recibe un mensaje comprensible.
- No se debilita la proteccion CSRF.
- Tests existentes siguen pasando.

Resultado:

- Mergeada a main.
- Security redirect activo.
- Mensaje CSRF personalizado.
- Vistas 400, 403 y 404 mas amigables.
- AuthFilter redirige al login con mensaje de sesion vencida.
- Tests agregados para seguridad, AuthFilter y mensaje CSRF.


FASE 7 - ABM de categorias - COMPLETADA

Rama:
feature/abm-categorias

Objetivo:
Permitir administrar categorias desde la aplicacion en lugar de mantenerlas fijas en codigo.

Alcance:

- Listar categorias.
- Crear categoria.
- Editar categoria.
- Activar/desactivar categoria.
- Usar solo categorias activas al crear/editar gastos.
- Mantener "Otros" como categoria obligatoria y protegida.
- Evitar eliminar categorias usadas por gastos.
- Definir migracion desde gastos.categoria textual hacia una estructura mas mantenible.

Decision tecnica clave:

Opcion A:
Mantener gastos.categoria como texto y crear tabla categorias solo como catalogo.

Ventajas:
- Menos disruptivo.
- Menor riesgo.
- Compatible con datos actuales.

Desventajas:
- Menos consistente.
- Reportes y joins futuros menos prolijos.

Opcion B:
Crear tabla categorias y migrar gastos a categoria_id.

Ventajas:
- Mas mantenible.
- Mejor para reportes.
- Permite colores, orden, estado activo/inactivo y metadata futura.

Desventajas:
- Requiere migracion de datos.
- Toca el nucleo de gastos.

Recomendacion:
Opcion B, si el esfuerzo es razonable. Como categorias se acaba de implementar, todavia es buen momento para ordenar el modelo antes de que crezca.

Criterios de aceptacion:

- Se pueden administrar categorias desde la UI.
- "Otros" existe siempre y no se puede eliminar.
- Las categorias inactivas no aparecen para nuevos gastos.
- Los gastos existentes conservan su categoria.
- Balance, dashboard y listado siguen funcionando.

Resultado:

- Mergeada a main.
- Se eligio Opcion B.
- Se creo tabla categorias.
- Se migraron gastos desde categoria texto a categoria_id.
- Se elimino la columna textual gastos.categoria.
- Se agrego FK con DELETE RESTRICT y UPDATE CASCADE.
- Se agrego pantalla de ABM de categorias.
- Se agrego link de Categorias en navbar.
- Crear/editar gastos usa solo categorias activas.
- "Otros" queda protegida.
- Tests ampliados para logica de categorias.


FASE 8 - Mejoras de flujo grupos/gastos - COMPLETADA

Rama:
feature/mejoras-flujo-grupos-gastos

Objetivo:
Reducir friccion en los flujos principales de uso.

Cambios:

- Crear grupo con miembros iniciales.
- Editar grupo con acceso claro a gestion de miembros.
- Al crear gasto, pagador automatico = usuario logueado.
- Al crear gasto, participantes por defecto = todos los miembros del grupo.
- Mantener division igualitaria actual.
- No implementar porcentajes todavia.

Notas:

- La seleccion manual de participantes puede quedar para una fase futura.
- Si se mantiene selector de participantes, deberia venir preseleccionado con todos los miembros.
- No conviene mezclar esto con porcentajes ni divisiones avanzadas.

Criterios de aceptacion:

- Crear un grupo permite sumar miembros iniciales o deja un flujo inmediato para hacerlo.
- Editar grupo tiene acceso claro a miembros.
- Crear gasto no obliga a elegir pagador si el pagador es el usuario logueado.
- Crear gasto selecciona todos los miembros por defecto.
- Reglas de grupo cerrado/liquidado siguen vigentes.

Estado:

- Mergeada a main.
- Auditoria realizada.
- Se agrego seleccion de miembros iniciales al crear grupo.
- Editar grupo tiene acceso claro a gestion de miembros.
- Crear gasto usa como pagador al usuario logueado.
- Crear gasto preselecciona todos los miembros del grupo como participantes.
- Se valido que los miembros iniciales existan antes de crear el grupo.
- Se normalizaron IDs para evitar duplicar al creador si llega manipulado en miembros[].
- Tests ampliados para flujo de grupos/gastos.

FASE 9 - Medios de cobro y pagos asistidos - COMPLETADA

Rama:
feature/medios-cobro

Objetivo:
Facilitar que los usuarios puedan saldar deudas desde la app sin integrar pagos reales todavia.

Idea general:

- Cada usuario puede cargar sus propios medios de cobro.
- La app no procesa dinero.
- La app ayuda a copiar datos de transferencia y, si existe, abrir un link externo.
- El pago real se hace fuera de SplitWise.
- Despues el usuario registra manualmente el pago en la app.

Entidad propuesta:

medios_cobro o user_payment_methods

Campos sugeridos:

- id.
- user_id.
- nombre.
- alias.
- cbu_cvu.
- link_pago.
- favorito.
- activo.
- created_at.
- updated_at.

Ejemplo:

- Nombre: Santander.
- Alias: fernando.santander.
- CBU/CVU: 000000...
- Link de pago: link externo opcional.
- Favorito: si.

Reglas:

- Cada usuario administra sus propios medios de cobro.
- Un usuario puede tener varios medios activos.
- Un usuario puede marcar un medio favorito.
- Si marca uno como favorito, los demas dejan de ser favoritos.
- No se muestran medios de cobro en forma global a todos los usuarios.
- Solo se muestran cuando hay una transferencia sugerida hacia ese usuario.

Pantallas / flujo:

- Nueva pantalla "Mis medios de cobro".
- Crear medio de cobro.
- Editar medio de cobro.
- Activar/desactivar medio de cobro.
- Marcar favorito.
- En balance/liquidacion, cuando aparece una deuda sugerida:
  - Mostrar boton "Pagar" o "Ver datos de pago".
  - Mostrar el medio favorito del receptor si existe.
  - Permitir copiar alias.
  - Permitir copiar CBU/CVU.
  - Si existe link_pago, permitir abrirlo.
  - Luego mantener el flujo actual de registrar pago manualmente.

UX esperada:

- Botones de copiado siempre visibles en contexto de pago.
- Pensado para uso desde celular.
- Evitar que el usuario tenga que seleccionar/copiar texto manualmente.
- Mensaje simple cuando se copia al portapapeles.

Seguridad / privacidad:

- Un usuario solo puede crear/editar/eliminar sus propios medios de cobro.
- Los medios de cobro solo se exponen a miembros de un grupo cuando hay una transferencia sugerida hacia ese usuario.
- No mostrar alias/CBU/CVU en listados generales de usuarios.
- No integrar APIs bancarias ni Mercado Pago en esta fase.

Criterios de aceptacion:

- Un usuario puede administrar sus medios de cobro.
- Puede definir un favorito.
- En transferencias sugeridas se puede ver el medio favorito del receptor.
- Se puede copiar alias y CBU/CVU con boton.
- Si hay link de pago, se puede abrir desde la app.
- No cambia la logica contable de pagos.
- Registrar pago sigue siendo manual.
- Tests existentes siguen pasando.

Resultado:

- Mergeada a main.
- Se creo la entidad user_payment_methods.
- Cada usuario puede administrar sus propios medios de cobro.
- Se agrego pantalla "Mis medios de cobro".
- Se permite cargar nombre, alias, CBU/CVU y link de pago.
- Se permite activar/desactivar medios de cobro.
- Se permite marcar un unico favorito por usuario.
- En balance/liquidacion se muestran datos de pago del receptor cuando hay transferencia sugerida.
- Se agregaron botones para copiar alias y CBU/CVU.
- Si existe link de pago, se puede abrir desde la app.
- Registrar pago sigue siendo manual.
- Tests ampliados para medios de cobro y pagos asistidos.


FASE 10 - Roles y permisos globales - COMPLETADA

Rama:
feature/roles-globales

Objetivo:
Controlar que puede ver y administrar cada usuario a nivel aplicacion.

Alcance inicial:

- Agregar rol global a usuarios.
- Diferenciar admin global / usuario comun.
- Solo admin global puede gestionar usuarios.
- Solo admin global puede gestionar categorias.
- Usuarios comunes solo ven grupos donde son miembros.
- Mantener roles por grupo para acciones dentro del grupo.

Notas:

- No confundir rol global con rol dentro de grupo.
- El rol global controla administracion general.
- El rol de grupo controla acciones dentro de un grupo especifico.

Criterios de aceptacion:

- Un usuario comun no puede administrar usuarios globales.
- Un usuario comun no puede administrar categorias.
- Un usuario comun solo ve sus grupos.
- Los admins de grupo mantienen permisos dentro de sus grupos.

Resultado:

- Mergeada a main.
- Se agrego rol global a users.
- Roles disponibles: admin y user.
- El primer usuario queda como admin inicial.
- Login guarda userRole en sesion.
- Se creo AdminFilter.
- Rutas de usuarios y categorias quedan protegidas con auth + admin.
- Navbar muestra Usuarios y Categorias solo a admin global.
- ABM de usuarios permite asignar rol.
- Se protege el ultimo admin para que no quede la app sin administradores.
- Tests ampliados para roles globales, filtro admin y sesion actualizada.


FASE 11 - Permisos por grupo - COMPLETADA

Rama:
feature/permisos-grupo

Objetivo:
Permitir configurar que puede hacer cada rol dentro de un grupo sin complicar el flujo simple de la app.

Idea general:

- Mantener la app simple por defecto.
- Usar roles por grupo existentes: admin/member.
- Agregar reglas configurables o helper centralizado para acciones del grupo.
- Evitar que cada controller tenga reglas duplicadas.

Acciones a controlar:

- Crear gastos.
- Editar gastos.
- Eliminar gastos.
- Registrar pagos.
- Editar pagos.
- Eliminar pagos.
- Agregar miembros.
- Quitar miembros.
- Cambiar roles.
- Cerrar grupo.
- Liquidar grupo.

Regla inicial sugerida:

- Admin puede todo dentro del grupo.
- Member puede crear gastos y registrar pagos.
- Member puede editar/eliminar solo movimientos propios, si se decide habilitarlo.
- Member no puede administrar miembros ni estado del grupo.

Decisiones a revisar:

- Si los permisos son fijos por rol o configurables por grupo.
- Si conviene empezar con helper centralizado antes de crear pantallas.
- Si se necesita una pantalla de configuracion de permisos por grupo.

Criterios de aceptacion:

- Las reglas estan centralizadas.
- No se rompe el flujo simple actual.
- Backend y UI aplican las mismas reglas.
- Los permisos no dependen solo de ocultar botones en la vista.

Estado:

- Mergeada a main.
- Se implemento app/Services/GroupPermission.php.
- Reglas centralizadas por rol de grupo, estado y owner del movimiento.
- Backend aplica permisos en grupos, gastos y pagos.
- UI oculta acciones segun permisos calculados.
- Member puede crear gastos/pagos.
- Member puede editar/eliminar solo movimientos propios.
- Admin de grupo puede administrar grupo, miembros, estado y movimientos.
- Estados cerrado/liquidado siguen teniendo prioridad sobre permisos.
- Tests unitarios agregados para reglas de permisos.
- Auditoria realizada.
- Se corrigio UI de pagador:
  - Nuevo pago muestra "Vos" como pagador.
  - Editar gasto/pago muestra pagador readonly para member.
  - Editar gasto/pago permite cambiar pagador solo a admin de grupo.
- Merge commit: 8bbd56e.


FASE 12 - Reportes y estadisticas - COMPLETADA

Rama:
feature/reportes

Objetivo:
Aprovechar categorias, grupos, gastos, pagos y balances para mostrar analisis mas util.

Ideas:

- Gastos por categoria.
- Evolucion por fecha.
- Saldo por grupo.
- Usuarios con mas gasto pagado.
- Usuarios con mas gasto consumido.
- Exportacion simple a CSV.

Criterios de aceptacion:

- Reportes claros y filtrables.
- No recalcular logica duplicada si ya existe en modelos/servicios.
- No romper dashboard ni balance.

Estado:

- Mergeada a main.
- Se agrego pantalla /reportes protegida por auth.
- Se agrego exportacion CSV en /reportes/exportar protegida por auth.
- Se agrego link "Reportes" en navbar.
- Se agrego app/Services/Reportes.php para calculos y consultas.
- Reportes muestra resumen, gastos por categoria, gastos por grupo y ultimos movimientos.
- Filtros disponibles: grupo, categoria, fecha desde y fecha hasta.
- Export CSV respeta los mismos filtros.
- Tests de logica pura cubren resumen, filtros, agrupacion por categoria, agrupacion por grupo y formato CSV.
- Auditoria realizada antes del merge.


FASE 13 - Auditoria de producto y simplificacion UX - COMPLETADA

Rama:
feature/auditoria-ux

Objetivo:
Revisar la experiencia general de la app despues de cerrar las funcionalidades principales, corregir fricciones simples y dejar registradas las mejoras grandes para fases futuras.

Alcance:

- Revisar navbar y organizacion de accesos.
- Revisar badges, textos y labels confusos.
- Revisar listados en mobile.
- Detectar duplicaciones de informacion.
- No tocar logica de negocio ni permisos.

Resultado:

- Mergeada a main.
- Navbar mas claro, con seccion de administracion.
- Badge admin global menos alarmista.
- Se agrego estado visible en cards de grupos.
- Se limpio contenido duplicado en formulario de grupo.
- Reportes tiene mejor vista mobile.
- Se registraron fricciones pendientes:
  - Recuperacion de contrasena.
  - Confirmaciones mas claras para acciones destructivas.
  - Dashboard mobile demasiado cargado.
  - Explicar categoria protegida.
  - Mejorar vista mobile de medios de cobro.
  - Previsualizacion de division igualitaria al cargar gasto.


FASE 14 - Documentacion navegable - COMPLETADA

Rama:
docs/documentacion-navegable

Objetivo:
Crear una documentacion tecnica navegable, estilo pagina web, para poder retomar el proyecto y explicar arquitectura, flujos, permisos y verificaciones sin depender solo del contexto conversacional.

Alcance:

- Crear documentacion estatica en public/doc/.
- Linkear la documentacion desde README.md.
- Documentar setup local.
- Documentar arquitectura MVC, modelos, controladores, services, filters y views.
- Documentar base de datos y entidades principales.
- Documentar flujos principales.
- Documentar permisos globales y permisos por grupo.
- Documentar reportes, testing, roadmap, troubleshooting y comandos.
- No subir Roadmap.md local.
- No incluir secretos ni datos privados.

Resultado:

- Mergeada a main.
- Documentacion disponible en:
  - http://localhost/SplitWise/doc/
  - public/doc/index.html
- Se agregaron assets estaticos propios:
  - public/doc/assets/styles.css
  - public/doc/assets/app.js
- No se modifico public/.htaccess.
- La app sigue funcionando y /doc/ se sirve como directorio real.
- Tests y rutas siguieron pasando.


FASE 15 - Redisenio operativo de pantallas - COMPLETADA

Rama:
feature/redisenio-operativo

Objetivo:
Aplicar el principio UX transversal a la pantalla principal y a las pantallas clave de la app.

Motivo:
La app ya tiene la base funcional completa. Ahora conviene ordenar la experiencia para que el uso diario sea directo: entrar, ver los grupos relevantes, entrar al grupo correcto, cargar gasto/pago o revisar movimientos.

Pantalla principal:

- Reemplazar el dashboard financiero como primera experiencia dominante.
- Mostrar arriba acciones principales:
  - Nuevo grupo.
  - Accesos secundarios utiles, como reportes o medios de cobro si corresponde.
- Mostrar lista de grupos ordenada por actividad reciente.
- Calcular o exponer ultima actividad de cada grupo:
  - ultimo gasto.
  - ultimo pago.
  - fallback: fecha de creacion o actualizacion del grupo.
- Mostrar grupos activos primero.
- Mostrar cerrados/liquidados al final o en seccion separada.
- Cada card de grupo debe mostrar:
  - nombre.
  - estado.
  - ultimo movimiento.
  - saldo del usuario en ese grupo.
  - accion principal: entrar/ver detalle.

Pantallas de grupo:

- Priorizar acciones principales:
  - Agregar gasto.
  - Registrar pago.
  - Ver balance.
- Mostrar movimientos recientes primero.
- Evitar duplicar informacion que ya esta completa en balance.
- Mantener gestion de miembros y estado disponibles, pero sin competir con el flujo principal.

Pantallas de gastos y pagos:

- Listas ordenadas por fecha reciente.
- Filtros visibles pero no invasivos.
- Cards mobile con descripcion, monto, grupo, fecha, estado/accion.
- Accion principal clara: ver detalle.

Pantallas administrativas:

- Usuarios, categorias y medios de cobro deben mantener estructura simple:
  - accion principal arriba.
  - lista/cards despues.
  - estados y restricciones explicadas en contexto.

Reportes:

- Mantenerlos como vista analitica secundaria.
- No reemplazan la home operativa.
- Deben tener filtros claros y resultados escaneables.

Criterios de aceptacion:

- La home muestra grupos ordenados por ultima actividad.
- La home deja de sentirse como tablero de metricas y pasa a ser una entrada operativa.
- Las pantallas principales comparten jerarquia visual: navbar, acciones, contenido.
- Mobile se resuelve con cards/listas, no con tablas comprimidas.
- No se rompe la logica de permisos, estados, balance ni reportes.
- Tests existentes siguen pasando.

Resultado:

- Mergeada a main.
- Home redisenada como entrada operativa.
- Grupos activos visibles como contenido principal.
- Grupos ordenados por actividad reciente.
- Cards muestran estado, saldo y ultimo movimiento.
- Grupos inactivos quedan menos prominentes.
- Pantalla de grupo reorganizada: acciones principales arriba, balance rapido, movimientos recientes y miembros compactos.
- Categorias y medios de cobro mejorados en mobile.
- Se corrigio query de ultima actividad para MySQL real.


FASE 16 - Confirmaciones destructivas - COMPLETADA

Rama:
feature/confirmaciones-destructivas

Objetivo:
Reemplazar confirm() genericos por modales o pantallas de confirmacion claras para eliminar grupos, gastos, pagos, categorias y medios de cobro.

Motivo:
Es una mejora de seguridad/UX de bajo riesgo. Evita errores de usuario y permite explicar consecuencias concretas antes de borrar.

Alcance sugerido:

- Confirmar eliminacion de grupo mostrando que se eliminan o afectan gastos, pagos y miembros relacionados.
- Confirmar eliminacion de gasto mostrando monto, grupo y participantes.
- Confirmar eliminacion de pago mostrando origen, destino, monto y grupo.
- Confirmar eliminacion/desactivacion de categoria explicando restricciones.
- Confirmar eliminacion/desactivacion de medio de cobro.
- Mantener backend igual de protegido; la UI no reemplaza permisos.

Resultado:

- Mergeada a main.
- Se agrego partial reutilizable para modal Bootstrap de confirmacion.
- Se reemplazaron confirm() genericos en acciones destructivas.
- Confirmaciones cubren eliminar grupo, quitar miembro, cerrar/reabrir/liquidar grupo, eliminar gasto, eliminar pago, desactivar/eliminar categoria y desactivar/eliminar medio de cobro.
- Se mantuvieron CSRF, metodos HTTP y permisos backend.


FASE 17 - Recuperacion de contrasena - COMPLETADA

Rama:
feature/recuperacion-password

Objetivo:
Agregar flujo "Olvide mi contrasena" para que la app sea mas usable sin intervencion manual del admin.

Motivo:
Es una friccion importante detectada en UX. Requiere decidir si se configura envio real de email o si se implementa primero con flujo local/dev.

Alcance sugerido:

- Link en login.
- Solicitud por email.
- Token de recuperacion con expiracion.
- Formulario para nueva contrasena.
- Mensajes seguros que no revelen si un email existe.
- Link de recuperacion visible solo en development.

Resultado:

- Mergeada a main.
- Se agrego tabla password_resets.
- Los tokens se generan con random_bytes, se guardan hasheados y expiran.
- Los tokens son de un solo uso.
- Al generar un nuevo token se invalidan tokens anteriores del usuario.
- El formulario de nueva contrasena toma el token desde la URL.
- En development se muestra un link de recuperacion para probar localmente.
- En production no se muestra el link; queda pendiente integrar SMTP real.
- Flujo validado manualmente en navegador:
  - pantalla "Olvide mi contrasena" visible.
  - link de desarrollo disponible.
  - cambio de contrasena y login posterior sin errores.


FASE 18 - Mejoras mobile finas - COMPLETADA

Rama:
feature/mejoras-mobile-finas

Objetivo:
Pulir detalles mobile detectados despues del redisenio operativo.

Resultado:

- Mergeada a main.
- Dashboard mobile mas compacto.
- Se oculto la metrica "Debes" en mobile para reducir ruido visual.
- Categorias protegidas explican por que no se pueden editar, desactivar ni eliminar.
- No se tocaron reglas de negocio, rutas, permisos ni migraciones.


FASE 19 - Preview de division igualitaria al cargar gasto - COMPLETADA

Rama:
feature/preview-division-gasto

Objetivo:
Mostrar en tiempo real como se divide un gasto entre participantes antes de guardarlo.

Resultado:

- Mergeada a main.
- El formulario de gastos muestra preview dinamico segun monto y participantes seleccionados.
- Se mantuvo la division igualitaria como unica regla.
- No se implementaron porcentajes ni divisiones avanzadas.
- Se valido que el preview no cambie la logica backend.


FASE 20 - Emails transaccionales SMTP - COMPLETADA

Rama:
feature/emails-transaccionales

Objetivo:
Enviar emails reales para recuperacion de contrasena y aviso posterior de cambio.

Resultado:

- Mergeada a main.
- Se agrego configuracion SMTP por variables de entorno.
- Se documento SMTP en env, README.md y public/doc/index.html.
- En development se mantiene dev_reset_link como fallback.
- El envio real de recuperacion fue probado manualmente.
- El aviso de cambio de contrasena fue probado manualmente.
- Se corrigio SMTPPort para tratarlo como entero.
- Los errores SMTP quedan controlados y no muestran pantalla tecnica.
- No se versionan credenciales reales.


FASE 21 - Limpieza de ramas y mantenimiento Git - COMPLETADA

Rama:
main

Objetivo:
Ordenar ramas locales/remotas ya mergeadas y dejar el repositorio facil de navegar.

Resultado:

- Main quedo sincronizado con origin/main.
- Se borraron ramas locales mergeadas.
- Se borraron o prunearon ramas remotas mergeadas.
- Se elimino la rama descartada feature/periodos.
- Solo quedan main local y origin/main remoto.
- Working tree limpio.


FASE 22 - Bugfix rutas de miembros - COMPLETADA

Rama:
bugfix/rutas-miembros

Objetivo:
Restaurar rutas de gestion de miembros que existen en el controller pero no aparecen registradas.

Motivo:
La auditoria de planificacion detecto un bug funcional: existian metodos en Grupos.php para agregar miembro, cambiar rol y quitar miembro, pero php spark routes no mostraba las rutas correspondientes. Con autoRoute desactivado, esos formularios terminaban en 404.

Alcance:

- Agregar rutas faltantes en app/Config/Routes.php:
  - POST /grupos/{id}/miembros -> Grupos::agregarMiembro.
  - POST /grupos/{id}/miembros/{userId}/rol -> Grupos::cambiarRol.
  - DELETE /grupos/{id}/miembros/{userId} -> Grupos::quitarMiembro.
- No se toco logica de permisos ni controllers.
- No se mezclo con mejoras de UX ni nuevas features.

Resultado:

- Las 3 rutas estan registradas y verificadas con php spark routes.
- php -l sin errores.
- Tests existentes: OK (202 tests, 391 assertions).
- Merge fast-forward a main y push a origin/main.
- Working tree limpio.


FASE 23 - Seguridad y hardening - COMPLETADA

Rama:
feature/seguridad-hardening

Objetivo:
Revisar y endurecer la configuracion de seguridad base del proyecto, sin cambiar logica de negocio ni permisos.

Alcance:

- Documentar encryption.key configurable desde .env en Encryption.php y env template.
- Subir minimo de contrasena a 8 caracteres en creacion (Usuarios::create), cambio admin (Usuarios::password) y recuperacion (PasswordResetController::cambiarPassword).
- Activar Session::$regenerateDestroy = true para mejor seguridad de sesion.
- Configurar defaultLocale = es, supportedLocales = [es, en], appTimezone = America/Argentina/Buenos_Aires.
- Agregar a .gitignore .claude/ y SplitWiseReviewerCommands.md.
- Corregir mojibake en env template: "contraseña de aplicación" en UTF-8 valido.
- Documentar encryption key y diferencias dev-vs-prod en README.md.

Resultado:

- Encryption key documentada en Encryption.php con instrucciones de generacion via php spark key:generate.
- Contrasenas nuevas y cambiadas requieren minimo 8 caracteres en los 3 puntos de entrada.
- Sesiones regeneran destruyendo el ID anterior.
- App configurada con locale es-AR y timezone Argentina.
- Working tree limpio (archivos locales ignorados).
- Mojibake corregido en env template.
- README actualizado con guia de encryption key y tabla Produccion vs desarrollo.
- Tests: OK (202 tests, 391 assertions).
- PR #3 mergeado a main.
- Merge fast-forward, tests OK, working tree limpio.


FASE 24 - Autogestion de usuario - COMPLETADA

Rama:
feature/autogestion-usuario

Objetivo:
Permitir que un usuario gestione datos propios sin depender del admin global.

Alcance:

- Nuevo controller Perfil con 3 metodos: index, editarNombre, cambiarPassword.
- Nueva vista perfil/index con informacion de cuenta, formulario de nombre y formulario de cambio de contrasena.
- 3 rutas nuevas protegidas con auth: GET /perfil, POST /perfil/editar-nombre, POST /perfil/cambiar-password.
- Link "Mi perfil" en dropdown de sesion del navbar.
- Cambio de nombre actualiza session->userName.
- Cambio de contrasena verifica contrasena actual con password_verify.
- Acceso a "Mis medios de cobro" desde el perfil.
- Email y rol son solo informativos, no editables.
- Usuario no puede cambiar su rol global.

Resultado:

- Controller y vista creados y funcionando.
- Rutas registradas y verificadas con php spark routes.
- php -l sin errores.
- Tests: OK (202 tests, 391 assertions).
- Merge fast-forward a main y push a origin/main.
- Working tree limpio.


FASE 25 - Paginacion y escalabilidad de listados - COMPLETADA

Rama:
feature/paginacion-listados

Objetivo:
Evitar que gastos, pagos, usuarios y categorias se vuelvan pesados con muchos datos.

Alcance:

- Gastos::index usa Gasto::paginate() en vez de findAll().
- Pagos::index usa Pago::paginate() en vez de findAll().
- Usuarios::index usa User::paginate() en vez de findAll().
- Categorias::index usa Categoria::paginate() en vez de findAll().
- Las 4 vistas agregan $pager->links() con paginacion Bootstrap.
- Filtros se preservan al navegar entre paginas (CI4 Pager copia $_GET).
- Sort links en gastos y pagos corregidos para generar URLs validas con filtros activos.

Resultado:

- 10 archivos modificados (2 modelos, 4 controladores, 4 vistas).
- php -l sin errores.
- Tests: OK (202 tests, 391 assertions).
- Sort links corregidos: agregado &amp; antes de http_build_query().
- Merge fast-forward a main y push a origin/main.
- Rama remota feature/paginacion-listados borrada.
- Working tree limpio.


--- REDISENIO MOBILE-FIRST ---

A partir de aca las fases apuntan a transformar la experiencia mobile
de Bootstrap responsive a diseno tipo app, sin cambiar backend,
permisos, balance ni logica contable.

Estrategia de ramas:

- El redisenio mobile se trabaja con una rama integradora:
  feature/mobile-redesign
- Esta rama nace desde main actualizado despues de mergear Fase 26.
- Las fases 27 a 31 son subfases del redisenio mobile.
- Cada subfase nueva debe tener una subrama propia creada desde feature/mobile-redesign.
- Las subramas se mergean hacia feature/mobile-redesign, no hacia main.
- No hace falta abrir PR para esos merges internos; se autorizan por auditoria y se hacen local/remoto contra la rama integradora.
- Cada subfase debe cerrar con commits propios, auditoria, merge interno a feature/mobile-redesign y actualizacion de este roadmap.
- Main queda como rama estable para fixes, seguridad y features funcionales no visuales.
- Si main recibe cambios mientras feature/mobile-redesign sigue abierta, actualizar feature/mobile-redesign con main antes de crear la siguiente subrama.
- No abrir PR final ni mergear feature/mobile-redesign a main hasta que el flujo mobile completo este validado.
- Cada informe debe tratar la fase como subfase mergeable a feature/mobile-redesign, no como fase lista para merge a main.

Estructura esperada:

- main
  - feature/mobile-redesign
    - feature/mobile-redesign-home
    - feature/mobile-redesign-grupo
    - feature/mobile-redesign-balance
    - feature/mobile-redesign-detalles

Nota:
Fase 27 se implemento directamente en feature/mobile-redesign antes de definir este flujo de subramas. A partir de Fase 28, usar subramas.

Criterio transversal de todas las fases mobile:
- Cada fase debe validarse en mobile real o viewport movil (375px-414px).
- No copiar marca ni assets de Splitwise; solo tomar referencia de experiencia de uso.
- No tocar backend, permisos, balance, migraciones ni logica contable salvo que sea estrictamente necesario y avisando antes.
- Desktop debe seguir funcional despues de cada fase, aunque prioriza mobile.
- No migrar a otro framework; mantener Bootstrap 5 + vanilla JS.


1. Fase 26 - Base visual mobile-first y CSS propio (COMPLETADA)

Estado: COMPLETADA
Branch: feature/base-visual-mobile
PR: #5
Merge commit: c9b9846
Fecha: 2026-06-13

Objetivo cumplido:
- Estilos inline extraidos de _head.php a public/assets/app.css
- Variables CSS definidas en :root: primary, secondary, accent, danger, warning, text, muted, bg, surface, border, radius-*, shadow-*, spacing-xs a spacing-xl, touch-target
- app.css vinculado desde _head.php con base_url()
- Compatibilidad desktop preservada, Bootstrap intacto
- Sin cambios en controllers, modelos, rutas ni migraciones
- Tests: 202 pruebas OK

Nota: --secondary agregado como correccion post-auditoria (commit 9d7bbdf).


2. Fase 27 - Navegacion mobile + FAB (COMPLETADA)

Estado: COMPLETADA (subfase dentro de feature/mobile-redesign)
Branch: feature/mobile-redesign
Commit: 4dd4bfa
Fecha: 2026-06-13
Nota de ramas: esta subfase se implemento directo en la rama integradora. Desde Fase 28 en adelante, usar subrama por subfase y merge interno a feature/mobile-redesign.

Objetivo cumplido:
- Bottom tab bar fija en mobile (<768px) con 5 items: Home, Grupos, Gastos, Perfil, Mas
- FAB en dashboard con icono +, apunta a /gastos/nuevo, solo visible en mobile
- Navbar desktop clasico intacto (d-none d-md-block)
- Offcanvas mobile (offcanvas-bottom) con opciones secundarias: Reportes, Pagos, Mis medios de cobro
- Admin ve Categorias/Usuarios en el offcanvas (mismo check userRole === 'admin')
- Logout por POST con formulario oculto (respeta CSRF)
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK

Rama integradora feature/mobile-redesign mergeada a main via PR #6.


3. Fase 28 - Home como grupos y actividad reciente (COMPLETADA)

Estado: COMPLETADA (subfase dentro de feature/mobile-redesign)
Subrama: feature/mobile-redesign-home
Merge commit: ba3380d
Fecha: 2026-06-14

Objetivo cumplido:
- Dashboard redisenado: grupos como contenido principal, metricas movidas a seccion colapsable "Resumen"
- Filtros rapidos "Todos / Activos / Cerrados" con JS sin recarga
- Cards de grupo con nombre, saldo destacado, badge de estado y ultimo movimiento
- Grupos inactivos colapsados al final (details)
- Desktop conserva informacion con layout adaptado
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK


4. Fase 29 - Grupo show compacto (COMPLETADA)

Estado: COMPLETADA (subfase dentro de feature/mobile-redesign)
Subrama: feature/mobile-redesign-grupo
Merge commit: 1f291b3
Fecha: 2026-06-14

Objetivo cumplido:
- Header combinado con nombre, saldo destacado (verde/rojo), badge estado, badge rol, boton "+ Gasto" prominente
- Movimientos colapsable en mobile, expandido en desktop (collapse d-md-block)
- Miembros: tabla desktop (d-none d-md-block) / cards mobile (d-md-none) con badge de rol y acciones full-width
- Estado del grupo colapsable en mobile
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK


5. Fase 30 - Balance visual (COMPLETADA)

Estado: COMPLETADA (subfase dentro de feature/mobile-redesign)
Subrama: feature/mobile-redesign-balance
Merge commit: 2305752
Fecha: 2026-06-14

Objetivo cumplido:
- Mobile: cards con avatar (inicial en circulo de color), nombre, saldo neto destacado (verde/rojo)
- Barra de progreso dual: azul (pago) + amarillo (consumio)
- Detalle expandible con collapse para Pagó, Consumió, Envio, Recibio
- Transferencias sugeridas con botones touch-friendly (min-height: 44px)
- Desktop mantiene tabla completa sin cambios
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK


6. Fase 31 - Detalles gasto/pago limpios (COMPLETADA)

Estado: COMPLETADA (subfase dentro de feature/mobile-redesign)
Subrama: feature/mobile-redesign-detalles
Merge commit: e07bd88
Fecha: 2026-06-14

Objetivo cumplido:
- Gasto show: header con monto grande, participantes como badges/chips en mobile, Editar secundario, Eliminar en menu contextual (tres puntitos) en mobile / visible en desktop
- Pago show: mismo patron con monto grande, "Pagador → Receptor", grupo, fecha
- Botones destructivos ocultos en menu en mobile para reducir densidad visual
- Desktop conserva disposicion actual con botones visibles
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK


--- REDISENIO MOBILE-FIRST COMPLETADO (MERGEADO) ---

Todas las fases del redisenio mobile (F26 a F31) estan implementadas y mergeadas a main.

- F26: Base visual (CSS propio, variables) — PR #5, mergeada a main
- F27 a F31: Flujo mobile completo — PR #6, mergeado a main via squash

PR #6: Rediseno mobile-first completo (Fases 27-31)
Rama: feature/mobile-redesign (eliminada post-merge)
Merge commit en main: 0d91c0b
Tests: 202 pruebas OK.
Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones en ninguna fase.


AJUSTES POST-REDISENIO MOBILE

Estos puntos salen de revisar la app en uso real despues del merge del redisenio mobile. No invalidan las fases cerradas, pero conviene tratarlos antes de sumar features grandes.

1. Pulido visual de home mobile (COMPLETADA)

Estado: COMPLETADA
PR: #7
Merge commit: 476df4c
Rama: feature/pulido-home-mobile (eliminada post-merge)
Fecha: 2026-06-14

Objetivo cumplido:
- Header mobile: nombre de usuario reemplazado por boton hamburguesa + offcanvas con Perfil, Reportes, Medios de cobro, Resumen
- FAB: cambiado a "Nuevo grupo" con icono + texto, apunta a /grupos/nuevo (fab-extended)
- Acciones secundarias (Reportes, Medios de cobro): ocultas en mobile, solo en menu hamburguesa. Desktop visible.
- Resumen colapsable: boton visible eliminado, accesible desde menu hamburguesa via Bootstrap Collapse API
- Filtros "Todos / Activos / Cerrados": redisenados como segmented control compacto con etiqueta "Grupos"
- Cards de grupo: fondo de pagina --surface-muted (#f6f8fa), bordes --border-strong (#d8e0e7), sombra --card-shadow
- Variables CSS nuevas: --surface-muted, --border-strong, --card-shadow
- Bugfix: actividad reciente ahora visible al filtrar por "Activos"
- Sin cambios en controllers, modelos, rutas, sesion, roles, permisos ni migraciones
- Tests: 202 pruebas OK

2. Pulido visual de grupo show mobile (COMPLETADA)

Estado: COMPLETADA
PR: #8
Merge commit: 095443e
Rama: feature/post-redisenio-pendientes (eliminada post-merge)
Fecha: 2026-06-14

Objetivo cumplido:
- Header compacto: accion "+ Gasto" destacada como boton primario, el resto (Balance, +Pago, Editar, Eliminar) movido a menu "Mas opciones" en mobile. Desktop conserva botones visibles.
- FAB "Agregar gasto" agregado en mobile, apunta a /gastos/nuevo?grupo_id=X
- Sin cambios en controllers, modelos, rutas, permisos, balance ni migraciones
- Tests: 202 pruebas OK

3. Carga rapida de gasto mobile (COMPLETADA)

Estado: COMPLETADA
PR: #8
Merge commit: 095443e
Rama: feature/post-redisenio-pendientes (eliminada post-merge)
Fecha: 2026-06-14

Objetivo cumplido:
- Formulario simplificado: solo descripcion, monto y fecha visibles en mobile
- "Mas opciones" colapsable con grupo, pagador, categoria y participantes
- Inferencia automatica de categoria desde la descripcion (diccionario de keywords en JS)
- Formateo de monto en vivo con formato argentino (punto de miles, coma decimal)
- Campo oculto monto_real para envio al backend
- Sin cambios en controllers, modelos, rutas, configuracion, permisos ni migraciones
- Tests: 202 pruebas OK


4. Ajustes UX post-redisenio (COMPLETADA)

Estado: COMPLETADA
PR: #9
Rama: feature/ux-post-redisenio
Fecha: 2026-06-14

Objetivo cumplido:
- Grupo show mobile: header informativo (nombre, saldo, estado, rol), todas las acciones en dropdown "Acciones"
- Home mobile: cards con sombra y borde mas visibles, etiqueta "Grupos" oculta en mobile (solo desktop)
- Gasto form: sin cambios adicionales (ya simplificado en item 3)
- Sin cambios en controllers, modelos, rutas, migraciones, permisos, balances ni logica contable
- Tests: 202 pruebas OK


4.1. Base tecnica de divisiones igualitarias (COMPLETADA)

Estado: COMPLETADA (subfase de Division avanzada)
PR: #10
Merge commit: f66aa60
Rama: feature/divisiones-base-tecnica (eliminada post-merge)
Fecha: 2026-06-15

Objetivo cumplido:
- Migracion: tabla gasto_divisiones (gasto_id, user_id, monto_calculado) + columna division_tipo en gastos
- Modelo GastoDivision con helper generarDivisionesIgualitarias()
- Gastos::create() genera divisiones explicitas al crear un gasto
- Balance actualizado: usa gasto_divisiones si existen, fallback a gasto_participantes si no
- Migracion de datos: genera divisiones para gastos existentes sin divisiones
- Tests: 206 pruebas OK (4 nuevas de redondeo y balance)
- Sin cambios en permisos, roles, rutas, UI, pagos ni deudas sugeridas


5. Division avanzada de gastos

Estado:
Pendiente.

Objetivo:
Permitir configurar como se divide un gasto entre los miembros del grupo, manteniendo por defecto el flujo simple: "Pagado por vos y dividido a partes iguales".

Vision de producto:
- La carga rapida sigue siendo el camino principal.
- El usuario no deberia ver complejidad salvo que toque el resumen de division.
- El resumen visible en el formulario debe decir algo como:
  - "Pagado por vos y dividido a partes iguales."
  - "Pagado por Fernando y dividido por monto fijo."
  - "Pagado por vos, Anto consume $20.000 y vos $10.000."
- Al tocar el resumen, se abre una pantalla o modal: "Como se dividio este gasto?"
- Desde esa pantalla se elige el modo de division.

Modos de division a soportar:

1. Partes iguales
- Es el modo por defecto.
- Todos los participantes del grupo consumen el mismo importe.
- En primera version, participan todos los miembros del grupo.
- Mas adelante puede permitir excluir participantes si se implementa division avanzada completa.

2. Monto fijo
- El usuario ingresa cuanto consume cada participante.
- La suma de importes debe coincidir con el monto total del gasto.
- Si falta o sobra, mostrar diferencia en tiempo real:
  - "Faltan asignar $2.000"
  - "Te pasaste por $1.500"
- Ideal para tickets donde cada persona consumio cosas distintas.

3. Por porcentaje
- El usuario define que porcentaje consume cada participante.
- La suma debe dar 100%.
- El sistema calcula el monto final de cada uno.
- Mostrar diferencia en tiempo real si no suma 100%.

4. Por cuotas / partes
- Cada participante tiene una cantidad de partes.
- Ejemplo: Fernando 1 parte, Anto 2 partes.
- El sistema divide el total por la suma de partes y calcula el consumo final.
- UI sugerida: steppers por persona (- 1 +).
- Util para consumos donde alguien cuenta doble, media parte, etc.

5. Ajuste
- Parte de una division base igualitaria y permite sumar/restar ajustes.
- La suma de ajustes debe dar 0.
- Ejemplo: dividir igual, pero a Fernando sumarle $5.000 y a Anto restarle $5.000.
- Es el modo mas dificil de explicar, por eso debe quedar para el final.

Datos y modelo sugerido:
- Crear tabla gasto_divisiones:
  - id
  - gasto_id
  - user_id
  - tipo
  - valor
  - monto_calculado
  - created_at
  - updated_at
- Agregar a gastos:
  - division_tipo
  - nota (opcional, puede ir en subfase posterior)
  - recibo_path (opcional, puede ir en subfase posterior)
- Mantener pagador_id como fuente de quien pago el gasto.

Impacto en balance:
- El calculo de consumido ya no debe asumir division igualitaria cuando existan divisiones explicitas.
- Nuevo criterio:
  - total_consumido_usuario = suma de gasto_divisiones.monto_calculado
  - total_pagado_usuario = suma de gastos donde pagador_id = usuario
- La formula general de saldo se mantiene:
  - saldo = total_pagado - total_consumido - pagos_recibidos + pagos_enviados
- Si un gasto no tiene divisiones cargadas, mantener fallback a division igualitaria para compatibilidad.

Compatibilidad con gastos existentes:
- Migrar gastos existentes generando divisiones igualitarias.
- Si la migracion no puede calcular algun caso, dejar fallback seguro para no romper balances.
- No eliminar datos historicos.
- Validar que balance y transferencias sugeridas sigan dando el mismo resultado para gastos viejos.

UX de nuevo gasto:
- Formulario principal:
  - Descripcion
  - Monto
  - Fecha
  - Resumen de division
- El resumen de division abre la configuracion avanzada.
- Grupo, pagador, categoria y participantes no deben llenar de ruido el flujo principal cuando ya se deducen por contexto.

Acciones inferiores del gasto:
- Agregar accesos tactiles secundarios al final de la pantalla:
  - Grupo actual
  - Fecha
  - Foto de recibo
  - Nota
- No todas tienen que implementarse juntas.
- Foto de recibo implica upload, storage y preview, por lo que puede quedar como subfase separada.

Subfases recomendadas:

4.1. Base tecnica de divisiones
- Crear tabla gasto_divisiones.
- Agregar division_tipo a gastos.
- Generar divisiones igualitarias para gastos nuevos.
- Migrar gastos existentes o asegurar fallback.
- Ajustar balance para consumir gasto_divisiones cuando existan.
- Tests fuertes de balance para no romper contabilidad.

4.2. UI simple con partes iguales explicitas
- Mantener el formulario limpio.
- Mostrar resumen "Pagado por vos y dividido a partes iguales".
- Guardar divisiones explicitas aunque el modo sea igualitario.
- No exponer todavia monto fijo, porcentaje, cuotas ni ajuste.

4.3. Monto fijo
- Agregar modo de division por monto fijo.
- Validar suma exacta contra total.
- Mostrar diferencia pendiente en tiempo real.
- Tests de redondeo y validacion.

4.4. Porcentaje
- Agregar modo de division por porcentaje.
- Validar suma 100%.
- Calcular montos con redondeo consistente.
- Tests de casos con decimales.

4.5. Cuotas / partes
- Agregar modo de division por partes.
- UI con steppers.
- Calcular consumo proporcional.
- Tests de partes enteras y casos borde.

4.6. Ajuste
- Agregar modo de ajuste sobre division base.
- Validar que la suma de ajustes sea 0.
- Mantener explicacion clara en UI.
- Dejarlo al final por complejidad conceptual.

4.7. Nota y recibo
- Agregar nota opcional al gasto.
- Agregar foto de recibo con upload, preview y eliminacion.
- Definir storage y validaciones de archivo.
- Mantener fuera del nucleo contable.

Fuera de alcance inicial:
- Procesamiento real de pagos.
- Integracion bancaria.
- OCR de recibos.
- Multiples monedas.
- Periodos.


FASES DIFERIDAS

Fase futura - Checklist de despliegue

Objetivo:
Documentar una lista de chequeo para un posible despliegue futuro, sin asumir que la app se va a publicar ahora.

Estado:
Diferida. No implementar todavia.

Motivo:
Por ahora el foco sigue siendo mejorar el producto local/mobile y la experiencia de uso. La preparacion de despliegue real solo tiene sentido cuando se decida publicar la app.

Alcance sugerido:

- Checklist de despliegue en documentacion.
- Revisar baseURL, indexPage, environment, logs y permisos de writable.
- Revisar SMTP real, HTTPS, backups y migraciones.
- Revisar que dev_reset_link no aparezca fuera de development.
- Revisar que errores tecnicos no queden expuestos al usuario final.
- No agregar features nuevas.
- No cambiar infraestructura local ni forzar HTTPS todavia.

Criterios de aceptacion:

- Existe checklist claro para despliegue futuro.
- Configuracion sensible queda documentada fuera de git.
- App mantiene tests y rutas OK.


Motivo del orden:

- El bug de rutas de miembros ya fue resuelto (Fase 22).
- Seguridad y hardening completado (Fase 23): encryption key, contraseñas 8 caracteres, sesión, locale/timezone Argentina.
- Autogestión de usuario completada (Fase 24): perfil propio, editar nombre, cambio de contraseña.
- Paginación completada (Fase 25): listados de gastos, pagos, usuarios y categorías con paginate().
- El despliegue real queda diferido hasta que haya decisión explícita de publicar la app.
- El redisenio mobile-first arranca con la base visual (F26) para que las fases siguientes ya usen estilos consistentes.
- La navegacion y el FAB (F27) son la estructura donde se apoyan el resto de las pantallas.
- Home (F28) y grupo show (F29) son las pantallas mas visitadas; conviene resolverlas temprano.
- Balance (F30) y detalles (F31) son pantallas de consulta; quedan al final del redisenio.
- Periodos sigue descartado para mantener la aplicacion simple.
- Procesamiento real de pagos queda fuera de alcance por ahora; los pagos asistidos manuales son suficientes para el producto actual.
- Nuevas features grandes deben salir de uso real, no de complejidad anticipada.
