# Roadmap Reportes y Analytics - SplitWise

Documento independiente del roadmap principal.

Objetivo: definir una línea de trabajo específica para reportes visuales, métricas automáticas y exportaciones. Este roadmap no reemplaza el roadmap funcional de la app ni debe mezclarse con fases de navegación, permisos, división avanzada o producción.

Principio central: los reportes deben ser útiles desde el minuto cero. El usuario no debería tener que configurar filtros cada vez que entra para entender su situación financiera.

---

## Estado actual

La app ya cuenta con:

- Usuarios.
- Grupos.
- Miembros por grupo.
- Gastos.
- Pagos.
- Categorías.
- Balance por grupo.
- Transferencias sugeridas.
- Estados de grupo.
- Medios de cobro.
- Reportes básicos.
- Exportación CSV básica.

Limitación actual:

- Los reportes todavía son más informativos que analíticos.
- Falta una experiencia visual moderna.
- Falta descargar reportes presentables en PDF.
- Falta exportación tipo Excel con varias hojas o datasets útiles.
- Falta comparación automática contra períodos anteriores usando fechas reales.

---

## Principios de diseño

### Sin configuración obligatoria

Cada pantalla de reportes debe abrir mostrando información útil por defecto:

- Mes actual.
- Comparación contra mes anterior.
- Grupos activos primero.
- Datos del usuario logueado.
- Categorías más relevantes.
- Pagos pendientes o sugeridos.

Los filtros pueden existir, pero deben ser secundarios.

### Mobile-first

En mobile:

- Cards compactas.
- Rankings simples.
- Barras horizontales.
- Métricas claras.
- Sin tablas anchas.
- Botones grandes para descargar.

En desktop:

- Se pueden mostrar tablas complementarias.
- Mantener la lectura principal visual.

### Visual, no corporativo

Evitar dashboards pesados o genéricos. La experiencia debe sentirse como una app financiera simple:

- Verde para saldo a favor.
- Rojo/naranja para deuda.
- Gris para información secundaria.
- Barras y rankings antes que gráficos complejos.
- Textos cortos y accionables.

### Exportaciones útiles

Todo reporte importante debe poder exportarse:

- PDF para compartir o archivar.
- Excel/CSV para análisis o respaldo.

---

## Datos base disponibles

Los reportes deben apoyarse en datos existentes:

- gastos
- pagos
- grupos
- grupo_miembros
- users
- categorias
- gasto_participantes o estructura equivalente actual
- balances calculados por los modelos existentes
- transferencias sugeridas existentes

No asumir que existe una tabla de períodos. Para comparaciones mensuales, usar fechas reales de gastos y pagos.

---

# Fase R1 - Reportes automáticos base (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Commit

4ab22be

## Objetivo

Rediseñar `/reportes` para que muestre un resumen útil automáticamente, sin que el usuario tenga que configurar nada.

## Alcance

Crear una pantalla de reportes con:

- Resumen del mes actual.
- Total gastado del mes.
- Total pagado del mes.
- Saldo neto del usuario.
- Cantidad de grupos con actividad.
- Top grupos por gasto.
- Top categorías por gasto.
- Últimos movimientos relevantes.
- Pagos pendientes o transferencias sugeridas principales.

## Reglas

- Por defecto mostrar datos del mes actual.
- Si no hay actividad este mes, mostrar el último mes con actividad.
- Si no hay datos, mostrar estado vacío claro.
- No exigir filtros para ver resultados.

## Fuera de alcance

- No implementar PDF.
- No implementar Excel.
- No cambiar modelos contables.
- No cambiar balance.
- No cambiar permisos.
- No cambiar dashboard principal.

## Criterios de aceptación

- `/reportes` carga una vista útil sin filtros.
- La pantalla responde: "cuánto gasté", "dónde gasté", "qué grupos se movieron" y "si debo o me deben".
- Mobile no usa tablas anchas.
- Desktop conserva buena lectura.
- Tests existentes pasan.

## Hito

El usuario puede entrar a Reportes y entender su situación general del mes en menos de 10 segundos.

---

# Fase R2 - Reporte por grupo (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Commit

b6f6e91

## Objetivo

Agregar una vista de reporte específica por grupo, pensada para entender qué pasó dentro de un grupo.

## Ruta sugerida

/grupos/{id}/reportes

## Alcance

Mostrar:

- Total gastado del grupo.
- Total pagado por cada miembro.
- Total consumido por cada miembro.
- Saldo neto por miembro.
- Gasto por categoría.
- Pagos registrados entre miembros.
- Transferencias sugeridas actuales.
- Evolución mensual del grupo.
- Últimos movimientos del grupo.

## Visualizaciones sugeridas

- Cards de resumen.
- Ranking de miembros.
- Barras horizontales por categoría.
- Lista de pagos entre usuarios.
- Línea simple o barras por mes para evolución.

## Reglas

- El reporte usa el grupo completo por defecto.
- Si el grupo tiene pocos datos, mostrar secciones igual pero con estado vacío.
- Mostrar saldos con color:
  - Verde: a favor.
  - Rojo/naranja: debe.
  - Gris: saldado.

## Fuera de alcance

- No cambiar cálculo de balance.
- No cambiar estados de grupo.
- No cambiar pagos ni gastos.
- No implementar PDF/Excel todavía.

## Criterios de aceptación

- Desde un grupo se puede acceder a su reporte.
- Se entiende quién pagó más, quién consumió más y qué falta saldar.
- La vista es clara en mobile.
- Tests existentes pasan.

## Hito

El usuario puede abrir un grupo y entender "quién bancó más" y "en qué se fue la plata".

---

# Fase R3 - Reportes por categoría (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Nota

Implementado como sección dentro de `/reportes` (top categorías con barras y rankings) y dentro de `/grupos/{id}/reportes` (detalle por categoría del grupo).

# Fase R4 - Comparaciones mensuales (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Nota

La evolución mensual se implementó dentro de `/grupos/{id}/reportes` con barras por mes usando `max()` como denominador. La comparación implícita mes actual vs anterior se logra via el resumen automático.

# Fase R5 - Reporte de pagos entre usuarios (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Nota

Implementado como sección "Pagos pendientes" en `/reportes` y "Transferencias sugeridas" en `/grupos/{id}/reportes`. Reutiliza `Gasto::computeDeudasFromBalance()` existente.

# Fase R6 - Exportación PDF (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Decisión técnica

Se instaló `dompdf/dompdf ^3.1` vía Composer. Compatible con CodeIgniter 4 y XAMPP.

## Ruta

`GET /reportes/exportar-pdf`

# Fase R7 - Exportación Excel (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Decisión técnica

CSV enriquecido con BOM UTF-8, encabezados claros, compatible con Excel/LibreOffice.

## Ruta

`GET /reportes/exportar`

# Fase R8 - Pulido visual y microcopy (COMPLETADA)

## Rama

feature/reportes-analytics-integral

## Nota

Aplicado sobre todas las vistas de reportes: cards compactas, barras de progreso, formato argentino, textos vacíos claros, aclaración de filtros, grupos linkeables, consistencia visual entre pantallas.

---

## Orden recomendado

1. R1 - Reportes automáticos base.
2. R2 - Reporte por grupo.
3. R3 - Reportes por categoría.
4. R4 - Comparaciones mensuales.
5. R5 - Reporte de pagos entre usuarios.
6. R6 - Exportación PDF.
7. R7 - Exportación Excel.
8. R8 - Pulido visual y microcopy.

---

## Criterios generales para todas las fases

Cada fase debe:

- Crear rama propia desde main.
- No hacer merge sin autorización.
- Mantener tests existentes pasando.
- No romper mobile.
- No romper desktop.
- No duplicar lógica contable.
- Reutilizar modelos y métodos existentes cuando sea posible.
- Mantener reportes útiles sin configuración obligatoria.
- Evitar tablas anchas en mobile.
- Mantener `Roadmap_Reportes_Analytics.md` fuera de Git.

---

## Verificaciones obligatorias por fase

- git status --short
- git log --oneline main..HEAD
- git diff --check main..HEAD
- php -l en archivos PHP tocados
- php vendor/bin/phpunit --no-coverage
- php spark routes
- php spark migrate:status si MySQL está corriendo
- búsqueda de mojibake en archivos tocados

---

## Fuera de alcance global

Este roadmap no incluye:

- Producción/deploy.
- Registro público.
- Períodos.
- División avanzada de gastos.
- Rediseño mobile general.
- Cambios de permisos.
- Integración real con bancos o Mercado Pago.
- Envío automático de reportes por email.

---

## Próxima fase sugerida

R1 - Reportes automáticos base.

Motivo:

- Tiene alto impacto visible.
- No requiere migraciones.
- Puede reutilizar datos existentes.
- Sirve como base para PDF y Excel.
- Mejora una pantalla que ya existe.
