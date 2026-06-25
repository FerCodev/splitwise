# Rutas

90 rutas registradas en app/Config/Routes.php, agrupadas por modulo.

## Sin filtro (publicas)

| Metodo | Ruta | Handler |
|---|---|---|
| GET | / | Auth::login |
| GET, POST | /login | Auth::login, Auth::doLogin |
| GET | /logout | Auth::logout |
| GET, POST | /password/olvidada | PasswordResetController |
| GET, POST | /password/reset/(:any) | PasswordResetController |
| GET | /doc, /doc/(:any) | Documentacion::index |
| GET | /documentacion, /documentacion/(:any) | Documentacion::index |

## Con filtro auth

| Metodo | Ruta | Handler |
|---|---|---|
| GET | /dashboard | Dashboard::index |
| GET, POST | /perfil, /perfil/editar, /perfil/cambiar-password | Perfil |
| GET, POST, PUT, DELETE | /grupos, /grupos/(:num) y variantes | Grupos |
| GET, POST, PUT, DELETE | /gastos, /gastos/(:num) y variantes | Gastos |
| GET, POST, PUT, DELETE | /pagos, /pagos/(:num) y variantes | Pagos |
| GET, POST, PUT, DELETE | /mis-medios-de-cobro y variantes | MediosCobro |
| GET | /reportes, /reportes/exportar, /reportes/exportar-pdf | Reportes |
| GET | /grupos/(:num)/reportes | Reportes::grupo |

## Con filtro auth + admin

| Metodo | Ruta | Handler |
|---|---|---|
| GET, POST, PUT, DELETE | /categorias y variantes | Categorias |
| GET, POST, PUT | /usuarios y variantes | Usuarios |
| GET, POST | /admin/catalogo-tarjetas y variantes | Admin |
