# Comandos para usar `splitwise`

Este archivo es una chuleta para manejar la skill `splitwise` en OpenCode con comandos cortos.

La idea es que no tengas que pegar prompts largos cada vez. Usas una frase breve, la skill lee el contexto del proyecto y arma el prompt, auditoria, revision o autorizacion correspondiente.

Repo correcto:

```text
C:\xampp\htdocs\SplitWise
```

Repo viejo que no debe usarse:

```text
F:\Fernando\fer\proyectos\SplitWise
```

## Reglas base

La skill deberia:

- Leer `Roadmap.md` cuando el pedido sea de roadmap, planificacion o proxima fase.
- Recordar que `Roadmap.md` es local y no debe subirse a GitHub.
- Separar bugs bloqueantes de mejoras de producto.
- Evitar fases gigantes.
- Mantener ramas chicas y verificables.
- No mergear sin autorizacion explicita.
- No borrar ramas no relacionadas.
- Si no hay PR y la fase esta lista, crear PR antes de mergear cuando quieras mantener revision formal.
- Si hay PR, revisar el PR antes de autorizar merge.
- Si solo hay rama local/remota, auditar la rama contra `main`.

## Flujo recomendado por fase

### 1. Pedir prompt para arrancar fase

```text
usa splitwise, lee el roadmap y dame el prompt para arrancar la siguiente fase
```

Debe devolver un prompt listo para pegarle al agente implementador.

El prompt deberia incluir:

- Rama sugerida.
- Objetivo.
- Alcance.
- Fuera de alcance.
- Criterios de aceptacion.
- Verificaciones obligatorias.
- Regla de no mergear sin autorizacion.

### 2. Auditar cuando el agente termina

```text
usa splitwise, audita esto
```

Debe revisar la rama, informe, captura o PR.

Debe responder con:

- Bloqueantes.
- Hallazgos menores.
- Verificaciones ejecutadas.
- Riesgos.
- Veredicto.
- Si corresponde, prompt de correccion.

Si puede, debe inspeccionar el repo real y no confiar solo en el resumen del agente.

### 3. Pedir prompt post-auditoria

```text
usa splitwise, armame el prompt post auditoria
```

Usalo cuando la auditoria encontro problemas.

Debe convertir los hallazgos en un prompt concreto para el agente, con:

- Archivos o zonas a revisar.
- Cambios esperados.
- Restricciones.
- Verificaciones.
- Pedido explicito de no mergear.

Si la auditoria no encontro problemas, la skill no deberia inventar correcciones. En ese caso deberia sugerir pedir autorizacion de merge.

### 4. Crear PR

```text
usa splitwise para crear PR de esta fase
```

Usalo cuando la rama ya fue auditada y queres abrir Pull Request antes del merge.

Si queres indicar la rama explicitamente:

```text
usa splitwise para crear PR de la rama feature/nombre-rama
```

Debe devolver un prompt para:

- Confirmar rama actual.
- Confirmar `git status --short` limpio.
- Confirmar que la rama esta basada en `main` actualizado.
- Correr verificaciones finales antes de publicar.
- Pushear la rama al remoto.
- Crear PR hacia `main`.
- Usar titulo y descripcion con resumen, alcance, verificaciones y riesgos.
- No mergear.
- Devolver link del PR.

### 5. Revisar PR

```text
usa splitwise para revisar el ultimo PR de esta fase
```

Usalo cuando ya existe un Pull Request.

Si queres indicar el PR explicitamente:

```text
usa splitwise para revisar el PR #X
```

Debe revisar:

- Rama origen y rama destino.
- Commits incluidos.
- Archivos modificados.
- Diff contra `main`.
- Tests y verificaciones reportadas.
- Cambios fuera de alcance.
- Riesgos de seguridad, permisos, CSRF, rutas, migraciones y datos.

Debe terminar con uno de estos veredictos:

- `Podes autorizar merge.`
- `Podes autorizar merge con observaciones menores.`
- `No autorizar merge todavia.`

### 6. Pedir prompt para autorizar merge

```text
usa splitwise para autorizar merge de los ultimos cambios
```

Usalo cuando la fase ya fue auditada y queres cerrar.

Importante: este comando debe generar texto para que vos lo pegues. No debe ejecutar el merge por su cuenta.

Si hay PR:

```text
usa splitwise para autorizar merge del PR #X
```

Si no hay PR:

```text
usa splitwise para autorizar merge de la rama feature/nombre-rama
```

El prompt deberia pedir:

- Confirmar rama actual.
- Confirmar `git status --short` limpio.
- Confirmar que `main` esta actualizado con `origin/main`.
- Confirmar que no hay conflictos.
- Mergear PR/rama a `main`.
- Pushear `main`.
- Borrar solo la rama remota de esa fase si corresponde.
- No borrar ramas no relacionadas.
- Correr verificaciones finales en `main`.
- Informar estado final.

### 7. Actualizar roadmap tras cerrar fase

```text
usa splitwise, actualiza roadmap con el cierre de esta fase
```

Debe marcar la fase como completada en `Roadmap.md`, registrar resultado, rama, PR o merge commit si los puede inferir, y dejar clara la siguiente fase pendiente.

Debe modificar solo `Roadmap.md`.

No debe tocar codigo, rutas, modelos, controllers, vistas, migraciones ni tests.

### 8. Planificar proximas fases

```text
usa splitwise, planifica las proximas fases desde el roadmap
```

Debe ordenar el trabajo futuro leyendo `Roadmap.md`.

Debe distinguir:

- Bugs bloqueantes.
- Mejoras de producto.
- Mejoras visuales.
- Tareas diferidas para produccion.

No deberia declarar fases definitivas sin pedir confirmacion cuando haya varias opciones razonables.

### 9. Revisar un prompt antes de ejecutarlo

```text
usa splitwise, revisa este prompt antes de implementarlo
```

Despues de esa frase, pega el prompt a revisar.

Debe detectar:

- Ambiguedades.
- Contradicciones.
- Alcance demasiado grande.
- Cambios peligrosos.
- Verificaciones faltantes.
- Archivos fuera de alcance.
- Riesgos de merge.

## Comandos rapidos

### Nueva fase

```text
usa splitwise, lee el roadmap y dame el prompt para arrancar la siguiente fase
```

### Auditoria

```text
usa splitwise, audita esto
```

### Correccion post-auditoria

```text
usa splitwise, armame el prompt post auditoria
```

### Revisar PR

```text
usa splitwise para revisar el ultimo PR de esta fase
```

o:

```text
usa splitwise para revisar el PR #X
```

### Crear PR

```text
usa splitwise para crear PR de esta fase
```

o:

```text
usa splitwise para crear PR de la rama feature/nombre-rama
```

### Autorizar merge

```text
usa splitwise para autorizar merge de los ultimos cambios
```

o:

```text
usa splitwise para autorizar merge del PR #X
```

o:

```text
usa splitwise para autorizar merge de la rama feature/nombre-rama
```

### Cerrar fase en roadmap

```text
usa splitwise, actualiza roadmap con el cierre de esta fase
```

### Planificacion

```text
usa splitwise, planifica las proximas fases desde el roadmap
```

## Si responde mal

Si inventa fases:

```text
usa splitwise. No inventes fases nuevas. Lee Roadmap.md y responde solo con la proxima fase pendiente y el prompt para ejecutarla.
```

Si mezcla un bug con una fase grande:

```text
usa splitwise. Separa el bug como hotfix corto y no mezcles mejoras de producto en la misma fase.
```

Si quiere mergear sin PR cuando vos queres PR:

```text
usa splitwise. No hagas merge directo. Crea o revisa el PR correspondiente y devolveme el link para auditar antes de mergear.
```

Si quiere borrar ramas de mas:

```text
usa splitwise. No borres ramas no relacionadas. Solo podes borrar la rama de esta fase despues de confirmar que el merge fue exitoso.
```

Si no leyo el repo real:

```text
usa splitwise. No confies solo en el informe pegado. Revisa el estado real del repo, diff contra main, rutas, tests y archivos tocados.
```
