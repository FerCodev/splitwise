# SplitWise — Proyecto actual

SplitWise es una aplicacion web para dividir gastos y administrar pagos entre grupos de personas. Permite crear grupos, registrar gastos con division personalizada, liquidar deudas mediante pagos entre miembros, y visualizar reportes y balances.

## Stack tecnologico

- **PHP** 8.2+
- **CodeIgniter** 4.7
- **MySQL** 5.7+ / MariaDB 10.3+ (MySQLi)
- **Bootstrap** 5.3.3
- **dompdf** para exportacion PDF
- **Sin framework JS frontend** (JavaScript vanilla con Bootstrap)

## Modulos principales

| Modulo | Descripcion |
|---|---|
| Auth | Login, logout y sesiones |
| Grupos | CRUD de grupos con miembros y roles |
| Gastos | CRUD de gastos con division igualitaria, monto fijo o porcentaje |
| Pagos | Registro de pagos entre miembros para saldar deudas |
| Categorias | Clasificacion de gastos, activable/desactivable |
| Medios de cobro | Datos bancarios/CVU/alias por usuario para facilitar pagos |
| Perfil | Edicion de nombre, email y cambio de contrasena |
| Reportes | Dashboard con resumen mensual, gastos por grupo/categoria, deudas |
| Usuarios | CRUD de usuarios (solo admin global) |
| Admin | Catalogo visual de componentes UI (solo admin) |
| Documentacion | Paginas de documentacion publica (sin login) |
| Password reset | Recuperacion de contrasena con token por email |

## Estado actual

- Autenticacion completa con roles (user/admin).
- Grupos con CRUD completo, roles por miembro, transiciones de estado (activo, cerrado, liquidado).
- Gastos con division avanzada y adjuntar recibo (imagen/PDF).
- Pagos con redireccion segun origen del formulario.
- Reportes con filtros, exportacion PDF/CSV.
- Catalogo visual de componentes elegibles con 10 contextos de alerta.
- Sistema de feedback por acciones con 34 acciones MVP mapeadas.
- Documentacion interna versionada (roadmaps, skill, proyecto).
- Documentacion publica navegable en /doc.
