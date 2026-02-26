# Actividades No Planificadas

`ActividadNoPlanificadaController` — Permite registrar actividades que surgieron durante la ejecución y no estaban en el plan original aprobado.

## ¿Qué es una Actividad No Planificada?

Una actividad no planificada (`es_no_planificada = true`) es aquella que:

- **No existía en el POA original** al momento de la aprobación.
- **Surgió durante la ejecución** del año fiscal, como respuesta a necesidades emergentes.
- Requiere **aprobación del Admin** (`estado_aprobacion = PENDIENTE` → `APROBADO`).

## Diferencias vs Actividades Planificadas

| Característica | Planificada | No Planificada |
|---|---|---|
| Aparece en el wizard | ✅ Sí | ❌ No |
| Requiere distribución mensual | ✅ Sí | ❌ No |
| Requiere aprobación del Admin | ❌ No | ✅ Sí |
| Cuenta para cumplimiento global | ✅ Sí | ❌ No |
| Cumplimiento se calcula como | `ejecutado/programado * 100` | `ejecutado > 0 ? 100% : 0%` |

## Rutas del Módulo

```
GET  /poa/actividades-no-planificadas/crear → Formulario de creación
POST /poa/actividades-no-planificadas/       → Guardar actividad
DELETE /poa/actividades-no-planificadas/{id} → Eliminar
```

## Consideración en Reportes

Las actividades no planificadas **sí aparecen** en los reportes Excel (POA Completo), en filas especiales marcadas como "no planificada" para distinguirlas visualmente de las planificadas.
