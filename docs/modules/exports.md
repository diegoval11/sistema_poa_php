# Exportaciones Excel y PDF

El sistema genera reportes oficiales del POA en dos formatos desde el panel de Admin.

## Tipos de Exportación

| Reporte | Servicio | Descripción |
|---|---|---|
| **POA Completo** | `PoaExcelService` | Detalle mensual completo con fórmulas de cumplimiento trimestrales y anuales |
| **POA Resumido** | `PoaExcelResumidoService` | Vista condensada con totales de recursos y resumen ejecutivo |
| **Detallado Admin** | `ProyectoDetalladoExport` | Exportación por proyecto individual con evidencias |
| **Global Trimestral** | `ExportController` | Estadísticas de todas las unidades en un período trimestral |

## Acceso a las Exportaciones

```
# Por unidad (desde UnidadController):
GET /admin/unidades/{id}/exportar-poa          → POA Completo
GET /admin/unidades/{id}/exportar-poa-resumido → POA Resumido

# Por proyecto individual (desde ProyectoAdminController):
GET /admin/proyectos/{id}/export-excel → Proyecto Detallado (.xlsx)
GET /admin/proyectos/{id}/export-pdf   → Proyecto Detallado (.pdf)

# Global (desde ExportController):
GET /admin/statistics/export-excel → Trimestral (.xlsx)
GET /admin/statistics/export-pdf   → Trimestral (.pdf)
GET /admin/unidades/export-excel   → Por unidades (.xlsx)
GET /admin/unidades/export-pdf     → Por unidades (.pdf)
```

## Nomenclatura de Archivos Generados

```
POA_Detallado_{Nombre_Unidad}_{Año}_{FechaHoy}.xlsx
// Ejemplo: POA_Detallado_Obras_Publicas_2026_20260223.xlsx
```

## Columna "Medio de Verificación o Causal del Incumplimiento"

En el Excel se muestra dinámicamente:
- Si `cantidad_ejecutada > 0` → muestra el **medio de verificación** de la actividad.
- Si `cantidad_ejecutada = 0` y `cantidad_programada > 0` → muestra la **causal del desvío**.
- Si no hay programación para ese mes → celda vacía.

::: tip Extensión
Para agregar un nuevo formato de exportación, crea un nuevo Service en `app/Services/` siguiendo el patrón de `PoaExcelService`, e intégralo en `UnidadController` o `ExportController`.
:::
