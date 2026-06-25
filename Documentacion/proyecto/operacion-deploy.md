# Operacion y deploy

## Comandos utiles

```text
php spark routes              -- Listar rutas registradas
php spark migrate             -- Ejecutar migraciones pendientes
php spark migrate:status      -- Ver estado de migraciones
php spark db:seed UserSeeder  -- Poblar datos de prueba
php spark key:generate        -- Generar encryption key
php vendor/bin/phpunit --no-coverage  -- Ejecutar tests
php -l archivo.php            -- Verificar sintaxis PHP
composer install              -- Instalar dependencias
git diff --check              -- Buscar errores de whitespace
git log --oneline main..HEAD  -- Commits de rama contra main
```

## Tests

214 tests, 467 assertions (PHPUnit 10).
Ejecutar con:

```text
php vendor/bin/phpunit --no-coverage
```

## Migraciones

21 migraciones disponibles. Crean y modifican las tablas:
users, grupos, grupo_miembros, gastos, gasto_participantes, gasto_divisiones, pagos, categorias, user_payment_methods, password_resets, ui_component_preferences, catalog_design_curations, ui_component_catalog_decisions.

Ejecutar despues de cada pull:

```text
php spark migrate
```

## Deploy manual

1. Backup de BD y archivos actuales.
2. Subir archivos del repo (excluyendo .env, app/Config/App.php local).
3. Ejecutar `composer install --no-dev --optimize-autoloader`.
4. Ejecutar `php spark migrate`.
5. Configurar .env del hosting con:
   - CI_ENVIRONMENT=production
   - app.baseURL apuntando al dominio
   - database.default.* con datos de la BD del hosting
   - encryption.key generado con php spark key:generate
   - email.* si se usa SMTP
6. Verificar que el DocumentRoot apunte a public/.
7. Verificar mod_rewrite habilitado para URLs limpias.
8. Verificar HTTPS y redirect de HTTP a HTTPS.

## .htaccess

El .htaccess del proyecto NO se versiona. Cada entorno (local, hosting) tiene su propio .htaccess con configuracion especifica:
- Local XAMPP: rewrite a public/ con base /SplitWise/.
- Hosting: rewrite a public/ con el dominio correspondiente, HTTPS forzado, seguridad de headers.

No incluir .htaccess del repo en produccion. Mantener el .htaccess propio del hosting.

## Entornos

| Variable | Development | Produccion |
|---|---|---|
| CI_ENVIRONMENT | development | production |
| Errores | Muestra detalles | Pantalla generica |
| forceGlobalSecureRequests | false | true |
| encryption.key | Opcional | Requerido |

## Notas de seguridad

- No versionar .env, app/Config/App.php local, backups, dumps SQL, zips, vendor, node_modules, logs.
- No exponer credenciales de BD ni API keys en el codigo.
- Las contrasenas se almacenan hasheadas con PASSWORD_DEFAULT.
- Tokens de recuperacion: un solo uso, expiracion 60 minutos, almacenados hasheados.
- CSRF protection activa en formularios.
- Filtros de autenticacion en rutas protegidas.
