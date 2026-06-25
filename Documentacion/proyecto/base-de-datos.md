# Base de datos

21 migraciones en app/Database/Migrations/. Motor: MySQL / MariaDB.

## Entidades principales

### users
- Columnas: id, name, email, password, role (user/admin), created_at, updated_at.
- Autenticacion por email + password hasheado.
- Role determina acceso a rutas admin.

### grupos
- Columnas: id, nombre, descripcion, estado (activo/cerrado/liquidado), created_by, created_at, updated_at.
- created_by es el usuario que creo el grupo (primer admin).

### grupo_miembros
- Columnas: grupo_id, user_id, rol (admin/member).
- Relacion N:N entre grupos y usuarios.
- Un grupo puede tener multiples admins.

### gastos
- Columnas: id, grupo_id, pagador_id, descripcion, monto, fecha, categoria_id, division_tipo (igualitario/monto_fijo/porcentaje), nota, recibo_path, recibo_nombre, recibo_mime, recibo_size, created_at, updated_at.

### gasto_participantes
- Columnas: gasto_id, user_id, monto_asignado.
- Participantes de cada gasto con su monto individual.

### gasto_divisiones
- Columnas: gasto_id, user_id, tipo (igualitario/monto_fijo/porcentaje), valor, monto_calculado.
- Configuracion de division por participante.

### pagos
- Columnas: id, grupo_id, pagador_id, receptor_id, monto, fecha, descripcion, created_at, updated_at.
- pagador_id entrega el dinero, receptor_id lo recibe.

### categorias
- Columnas: id, nombre, activa (boolean).
- "Otros" es la categoria protegida por defecto (id = 1).

### user_payment_methods
- Columnas: id, user_id, tipo, nombre, alias, cbu_cvu, banco, titular, payment_link, activo, favorito.
- Cada usuario puede tener multiples medios, pero solo uno favorito.

### password_resets
- Columnas: id, user_id, token (hasheado), expires_at, used_at, created_at.
- Tokens de un solo uso con expiracion.

### ui_component_preferences
- Columnas: id, screen_key, component_key, variant_key, user_id (nullable), created_at, updated_at.
- Permite personalizacion por usuario o global.

### catalog_design_curations
- Columnas: id, design_id, design_name, design_group, status, redesign_note, created_by, created_at, updated_at.
- Estado de curaduria de disenos del catalogo visual.

### ui_component_catalog_decisions
- Columnas: id, catalog_key, section_key, group_key, item_key, item_name, source_label, decision, redesign_notes, created_by, created_at, updated_at.
- Decisiones (implementar/descartar/redisenar) sobre componentes del catalogo.
