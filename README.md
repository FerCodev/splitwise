# SplitWise

Aplicación web para dividir gastos y administrar pagos entre grupos de personas.

## Requisitos

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Extensiones PHP: `intl`, `mbstring`, `mysqli`, `json`, `curl`

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/splitwise.git
cd splitwise

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp env .env
```

Editar `.env` con los datos locales:

```ini
app.baseURL = 'http://localhost/SplitWise/'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = splitwise
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
```

### Configuracion por entorno

No subir `.env` al repositorio. Cada entorno tiene el suyo:

```ini
# Local XAMPP o red local
CI_ENVIRONMENT = development
app.baseURL = 'http://192.168.0.9/SplitWise/'
app.indexPage = ''

# Hosting
CI_ENVIRONMENT = production
app.baseURL = 'https://tudominio.com/'
app.indexPage = ''
app.forceGlobalSecureRequests = true
```

Para desplegar cambios, subir el codigo versionado y mantener el `.env` propio del hosting con sus datos de dominio y base de datos.

```bash
# 4. Generar clave de encriptación (opcional, requerido si se usa Encryption)
php spark key:generate
# Copiar el resultado en .env como: encryption.key = hex2bin:....

# 5. Ejecutar migraciones y seed
php spark migrate
php spark db:seed UserSeeder

# 6. Iniciar servidor de desarrollo (opcional)
php spark serve
```

## Email (SMTP)

Para habilitar el envío real de emails (recuperación de contraseña, notificaciones), descomentar en `.env` y configurar según tu proveedor:

**Gmail:**
```ini
email.protocol = smtp
email.fromEmail = tu-email@gmail.com
email.fromName = SplitWise
email.SMTPHost = smtp.gmail.com
email.SMTPUser = tu-email@gmail.com
email.SMTPPass = tu-contrasena-de-aplicacion
email.SMTPPort = 587
email.SMTPCrypto = tls
```

**Outlook / Hotmail:**
```ini
email.protocol = smtp
email.fromEmail = tu-email@outlook.com
email.fromName = SplitWise
email.SMTPHost = smtp.office365.com
email.SMTPUser = tu-email@outlook.com
email.SMTPPass = tu-contrasena
email.SMTPPort = 587
email.SMTPCrypto = tls
```

Importante:
- `fromEmail` debe coincidir con `SMTPUser` (salvo alias configurado en el proveedor).
- Para Gmail se requiere contraseña de aplicación (sin espacios), no la contraseña de la cuenta.
- No se puede mezclar `smtp.gmail.com` con una cuenta de Outlook/Hotmail, ni viceversa.
- Requisitos mínimos: `protocol=smtp`, `SMTPHost`, `SMTPUser` y `SMTPPass` no vacíos.

## Encryption key

Algunas funcionalidades de CodeIgniter requieren una clave de encriptación. Para generarla:

```bash
php spark key:generate
```

Luego copiar el resultado en `.env`:

```ini
encryption.key = hex2bin:el-valor-generado
```

Si no se configura, las funcionalidades que dependen de Encryption pueden fallar. Es **requerido en producción**.

### Comportamiento por ambiente

| Ambiente | SMTP configurado | Comportamiento |
|---|---|---|
| `development` | No | Se muestra enlace de recuperación en pantalla (`dev_reset_link`) |
| `development` | Sí | Se envía email real + se muestra enlace en pantalla |
| `production` | No | Error controlado sin detalles técnicos |
| `production` | Sí | Se envía email real |

- En `development` siempre se puede probar el flujo aunque no haya SMTP.
- Si SMTP falla en `production`, el usuario ve un mensaje amigable y no hay exposición de errores.
- El envío de notificación de cambio de contraseña es no bloqueante: no impide completar el cambio.

## Documentación

La documentación completa del proyecto (arquitectura, base de datos, flujos, permisos, testing, roadmap y troubleshooting) está disponible en:

- **Web:** [`/doc/`](http://localhost/SplitWise/doc/)
- **Local:** `public/doc/index.html`

## Rutas principales

| Método | Ruta              | Descripción                |
|--------|-------------------|----------------------------|
| GET    | `/`               | Login                      |
| POST   | `/login`          | Iniciar sesión             |
| GET    | `/logout`         | Cerrar sesión              |
| GET    | `/dashboard`      | Panel principal            |
| GET    | `/grupos`         | Listar grupos              |
| POST   | `/grupos`         | Crear grupo                |
| GET    | `/grupos/{id}`    | Ver detalle de grupo       |
| GET    | `/gastos`         | Listar gastos              |
| POST   | `/gastos`         | Crear gasto                |
| GET    | `/pagos`          | Listar pagos               |
| POST   | `/pagos`          | Crear pago                 |

## Estructura del proyecto

```
app/
├── Config/          -- Configuración (rutas, base de datos, etc.)
├── Controllers/     -- Controladores (Auth, Dashboard, Grupos, Gastos, Pagos)
├── Database/
│   ├── Migrations/  -- Migraciones de la base de datos
│   └── Seeds/       -- Seeders (Usuario demo)
├── Filters/         -- Filtros (autenticación)
├── Models/          -- Modelos de datos
└── Views/           -- Vistas
    ├── partials/    -- Layout parcial (_head, _navbar, _footer)
    ├── grupos/
    ├── gastos/
    └── pagos/
```

## Tests

```bash
php vendor/bin/phpunit --no-coverage
```

## Producción vs desarrollo

| Configuración | Development | Producción |
|---|---|---|
| `CI_ENVIRONMENT` | `development` | `production` |
| Errores | Muestra detalles técnicos | Pantalla genérica sin stacktrace |
| Enlace recuperación | Se muestra en pantalla (`dev_reset_link`) | Solo por email si SMTP configurado |
| `encryption.key` | Opcional | Requerido |
| Contraseña mínima | 8 caracteres | 8 caracteres |
| Duración de sesión | 30 días | 30 días |
| Sesión `regenerateDestroy` | `true` | `true` |
| Timezone | `America/Argentina/Buenos_Aires` | `America/Argentina/Buenos_Aires` |
| Locale | `es` | `es` |
| `forceGlobalSecureRequests` | `false` | `true` (requiere HTTPS) |

## Servidor de producción

Configurar el web server para que apunte al directorio `public/`. Ejemplo para Apache:

```apache
DocumentRoot "C:/xampp/htdocs/SplitWise/public"
```

Asegurar que `mod_rewrite` esté habilitado para URLs limpias.
