# Tests

## Estado actual

La suite de tests automatizados está en etapa inicial.
Actualmente hay dos tests unitarios que validan la configuración base del proyecto.

## Requisitos

- PHPUnit (instalado vía Composer)
- No requiere base de datos

## Ejecutar

```console
vendor\bin\phpunit --no-coverage
```

Para generar reporte de cobertura (requiere XDebug con `xdebug.mode=coverage`):

```console
vendor\bin\phpunit --coverage-text
```

## Próximos pasos

Para agregar tests de base de datos (modelos, controladores):

1. Configurar una base de datos para el entorno `testing` en `.env` o `app/Config/Database.php`
2. Crear test cases que extiendan `CodeIgniter\Test\CIUnitTestCase`
3. Usar `DatabaseTestTrait` para migraciones y seeds automáticos

## Tests actuales

| Test | Archivo | Descripción |
|------|---------|-------------|
| `SecurityConfigTest` | `tests/unit/SecurityConfigTest.php` | Verifica que CSRF esté habilitado con configuración esperada |
| `FiltersConfigTest` | `tests/unit/FiltersConfigTest.php` | Verifica que el filtro CSRF esté activo en `$globals['before']` |
