# UI, Catalogo y Feedback

## Catalogo visual de componentes

El sistema de catalogo visual permite definir, seleccionar y personalizar componentes UI por pantalla.

Archivos principales:
- `app/Catalog/componentes.php` -- Indice del catalogo.
- `app/Catalog/Components/` -- Definiciones de componentes por pantalla.
- `app/Services/UiComponentResolver.php` -- Resuelve que variante usar para cada componente.
- `app/Models/UiComponentPreference.php` -- Persiste preferencias de variante.
- `app/Controllers/Admin.php` -- Interfaz admin del catalogo.

### Pantallas del catalogo

| Pantalla | Componentes |
|---|---|
| alertas | 10 contextos (success, error, warning, info, destructive, empty, security, process, payment, group) |
| home | home_group_card, debt_card |
| grupos | group_movement_card, group_gauge |
| gastos | filtered_total_card |
| pagos | filtered_total_card |
| medios | payment_method_card |

### Componentes de alerta por contexto

- alert_success: 7 variantes (success_compact, settled, favorite_updated, import_ready, backup_created, synced, member_added)
- alert_error: 3 variantes
- alert_warning: 5 variantes
- alert_info: 4 variantes
- alert_destructive_confirmation: 2 variantes
- alert_empty_state: 2 variantes
- alert_security_session: 2 variantes
- alert_process_export: 2 variantes
- alert_payment_suggestion: 2 variantes
- alert_group_event: 3 variantes

Total: 30 variantes de alerta, cada una en su propio archivo PHP dentro de `app/Catalog/Components/alertas/variants/`.

### Decisiones de catalogo

El admin puede marcar componentes como:
- **Implementar**: aprobado para uso.
- **Descartar**: no se usara.
- **Redisenar**: requiere cambios.

Las decisiones se persisten en `ui_component_catalog_decisions` y `catalog_design_curations`.

## Sistema de feedback por acciones

El sistema mapea acciones del usuario a mensajes de feedback mediante un modelo accion -> slot funcional -> componente visual.

Archivos principales:
- `app/Config/UiFeedback.php` -- Configuracion de slots y mapeo de acciones.
- `app/Services/UiFeedbackResolver.php` -- Resuelve mensajes y componentes.

### Slots funcionales

| Slot | Proposito |
|---|---|
| feedback.success | Operacion completada exitosamente |
| feedback.error | Operacion fallida |
| feedback.warning | Advertencia o condicion no bloqueante |
| feedback.info | Informacion neutral |
| confirmation.destructive | Confirmacion antes de accion irreversible |
| confirmation.warning | Confirmacion antes de accion importante |

### Acciones MVP migradas (34)

- auth.login.failed
- profile.update.completed/failed
- profile.password.change.completed/failed
- groups.* (create, update, delete, close, reopen, liquidate, member add/remove) -- 16 acciones
- expenses.* (create, update, delete, receipt delete) -- 8 acciones
- payments.* (create, update, delete) -- 6 acciones
