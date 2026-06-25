# Funcionalidades

## Autenticacion

- Login con email y contrasena.
- Las contrasenas se almacenan hasheadas con `password_hash()`.
- Sesion con regeneracion de ID.
- Roles: `user` (usuario comun) y `admin` (administrador global).
- Los admins globales no pueden ser miembros de grupos.
- Logout destruye la sesion.

## Perfil

- Visualizacion de datos personales (nombre, email, rol).
- Edicion de nombre y email.
- Cambio de contrasena verificando la contrasena actual.
- El email no puede repetirse entre usuarios.

## Grupos

- CRUD completo.
- Estados: activo, cerrado, liquidado.
- Transiciones validas: activo <-> cerrado, activo -> liquidado.
- No se puede liquidar un grupo con deudas pendientes.
- Roles por miembro: admin (del grupo) y member.
- Agregar/quitar miembros.
- Balance de grupo con detalle de deudas.
- Vista de actividad con movimientos y filtros por fecha/categoria/persona.

## Gastos

- CRUD completo.
- Division del gasto: igualitario, monto fijo, porcentaje.
- Validacion de que los participantes pertenezcan al grupo.
- Categoria asignable (con fallback a "Otros").
- Adjuntar recibo (JPG, PNG, WebP, PDF, max 5MB).
- Nota opcional.
- Exportacion a PDF y Excel.

## Pagos

- CRUD completo.
- Pagador es siempre el usuario logueado (salvo admin).
- Receptor es otro miembro del grupo.
- Validacion: pagador y receptor no pueden ser la misma persona.
- Origen del formulario determina redireccion post-guardado.
- Exportacion a PDF y Excel.

## Categorias

- CRUD completo (solo admin global).
- Activable/desactivable.
- La categoria "Otros" esta protegida (no se edita ni elimina).
- No se puede eliminar una categoria con gastos asociados.

## Medios de cobro

- CRUD por usuario (cada usuario ve solo los suyos).
- Campos: tipo, nombre, alias, CBU/CVU, banco, titular, link de pago.
- Alias o CBU/CVU son obligatorios (al menos uno).
- Activable/desactivable.
- Un medio favorito por usuario.
- Exportable desde balance de grupo.

## Reportes

- Resumen global del usuario.
- Resumen mensual con filtros (grupo, categoria, fecha).
- Top de grupos y categorias por gasto.
- Gastos por grupo y por categoria.
- Deudas pendientes.
- Exportacion a PDF y CSV.

## Admin (solo admin global)

- CRUD de usuarios.
- CRUD de categorias.
- Catalogo visual de componentes (app/Catalog/).
- Gestion de decisiones de diseno (implementar, descartar, redisenar).
- Curaduria de propuestas visuales.

## Documentacion publica

- Paginas navegables sin login bajo /doc.
- Indice, roadmaps, comandos de skill SplitWise.
- Render de Markdown y HTML controlado.
- Allowlist estricta de archivos publicables.
