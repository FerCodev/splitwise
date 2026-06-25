# Arquitectura tecnica

## Framework

CodeIgniter 4.7 siguiendo el patron MVC (Modelo-Vista-Controlador).

## Estructura de carpetas

```
app/
  Config/       -- Configuracion (rutas, base de datos, email, etc.)
  Controllers/  -- Controladores (14 clases)
  Database/
    Migrations/ -- Migraciones de BD (21 archivos)
    Seeds/      -- Seeders
  Filters/      -- Filtros de autenticacion (AuthFilter, AdminFilter)
  Helpers/      -- Funciones auxiliares (format_helper con numero_arg y moneda)
  Language/     -- Traducciones
  Libraries/    -- Librerias personalizadas
  Models/       -- Modelos de datos (12 modelos activos)
  Services/     -- Servicios de logica de negocio (4 servicios)
  ThirdParty/   -- Librerias de terceros
  Views/        -- Vistas (14 carpetas + 2 archivos directos)
Catalog/        -- Catalogo de componentes visuales (6 pantallas, 10 alertas)
Documentacion/  -- Documentacion interna versionada
public/         -- Document root web (index.php, assets, doc/)
  assets/       -- CSS, JS, imagenes
  doc/          -- Pagina estatica de documentacion HTML
writable/       -- Archivos temporales (logs, cache, sesiones, uploads)
```

## Controladores principales

| Controlador | Metodos clave | Filtro |
|---|---|---|
| Auth | login, doLogin, logout | -- |
| Dashboard | index | auth |
| Grupos | index, create, show, update, delete, cambiarEstado, agregarMiembro, quitarMiembro | auth |
| Gastos | index, create, show, update, delete, recibo, deleteRecibo, exportarPdf, exportarExcel | auth |
| Pagos | index, create, show, update, delete, exportarPdf, exportarExcel | auth |
| Categorias | index, create, update, toggle, delete | auth + admin |
| MediosCobro | index, create, update, toggle, favorito, delete | auth |
| Perfil | index, editar, actualizar, password, cambiarPassword | auth |
| Reportes | index, grupo, exportar, exportarPdf | auth |
| Usuarios | index, create, update, password | auth + admin |
| Admin | catalogoTarjetas, guardarComponente, guardarDecisionCatalogo, guardarCuraduria | auth + admin |
| Documentacion | index | -- (publico) |
| PasswordReset | olvidada, enviarEnlace, reset, cambiarPassword | -- |

## Modelos principales

| Modelo | Tabla | Proposito |
|---|---|---|
| User | users | Usuarios del sistema |
| Grupo | grupos | Grupos de gastos compartidos |
| GrupoMiembro | grupo_miembros | Miembros de cada grupo |
| Gasto | gastos | Gastos registrados |
| GastoParticipante | gasto_participantes | Participantes de cada gasto |
| GastoDivision | gasto_divisiones | Configuracion de division por gasto |
| Pago | pagos | Pagos entre miembros |
| Categoria | categorias | Categorias de gastos |
| PasswordReset | password_resets | Tokens de recuperacion de contrasena |
| UserPaymentMethod | user_payment_methods | Medios de cobro por usuario |
| UiComponentPreference | ui_component_preferences | Preferencias de variantes UI |
| UiComponentCatalogDecision | -- | Decisiones de catalogo visual |
| CatalogDesignCuration | catalog_design_curations | Curaduria de disenos |

## Servicios

| Servicio | Proposito |
|---|---|
| GroupPermission | Autorizacion de acciones dentro de grupos |
| Reportes | Logica de reportes y estadisticas |
| UiComponentResolver | Resolucion de variantes de componentes UI |
| UiFeedbackResolver | Resolucion de mensajes de feedback por accion |

## Helpers

- `numero_arg()`: formato de numero con separador de miles y coma decimal.
- `moneda()`: igual que numero_arg pero con simbolo $.
