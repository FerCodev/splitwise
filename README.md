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

```bash
# 4. Ejecutar migraciones y seed
php spark migrate
php spark db:seed UserSeeder

# 5. Iniciar servidor de desarrollo (opcional)
php spark serve
```

## Documentaci&oacute;n

La documentaci&oacute;n completa del proyecto (arquitectura, base de datos, flujos, permisos, testing, roadmap y troubleshooting) est&aacute; disponible en:

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

## Servidor de producción

Configurar el web server para que apunte al directorio `public/`. Ejemplo para Apache:

```apache
DocumentRoot "C:/xampp/htdocs/SplitWise/public"
```

Asegurar que `mod_rewrite` esté habilitado para URLs limpias.
