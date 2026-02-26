# Referencia de Rutas (web.php)

Mapa completo de todas las rutas del sistema agrupadas por módulo.

## Rutas Públicas / Auth

```
GET  /           → Redirige a /login
GET  /login      → Formulario de login
POST /login      → Procesar login
POST /logout     → Cerrar sesión
```

## Zona de Unidad (`role:unidad`)

### Dashboard
```
GET /dashboard   → Dashboard de la unidad con estadísticas de cumplimiento
```

### Wizard de Planificación
```
GET|POST  /poa/wizard/inicio/{id?}              → Paso 1: Datos del proyecto
GET       /poa/wizard/{id}/metas                → Paso 2: Ver metas
POST      /poa/wizard/{id}/metas                → Paso 2: Agregar meta
PUT       /poa/wizard/actualizar-meta/{id}      → Editar meta
DELETE    /poa/wizard/eliminar-meta/{id}        → Eliminar meta
GET       /poa/wizard/{id}/actividades          → Paso 3: Ver actividades
POST      /poa/wizard/{id}/actividades          → Paso 3: Agregar actividad
PUT       /poa/wizard/actualizar-actividad/{id} → Editar actividad
DELETE    /poa/wizard/eliminar-actividad/{id}   → Eliminar actividad
GET|POST  /poa/wizard/{id}/programacion         → Paso 4: Cronograma
GET       /poa/wizard/{id}/resumen              → Paso 5: Resumen
POST      /poa/wizard/{id}/finalizar            → Finalizar wizard
POST      /poa/wizard/{id}/enviar               → Enviar a revisión
POST      /poa/wizard/{id}/eliminar             → Eliminar proyecto
```

### Avances
```
GET  /poa/avances/{proyectoId}                        → Ver avances del proyecto
POST /poa/avances/update                              → Actualizar cantidad ejecutada
POST /poa/avances/update-causal                       → Guardar causal de incumplimiento
POST /poa/avances/store-evidencia                     → Subir evidencia
GET  /poa/avances/evidencias-mes/{actividadId}/{mes}  → Obtener evidencias (JSON)
```

### Actividades No Planificadas
```
GET    /poa/actividades-no-planificadas/crear → Formulario de creación
POST   /poa/actividades-no-planificadas/      → Guardar actividad no planificada
DELETE /poa/actividades-no-planificadas/{id}  → Eliminar
```

## Zona de Admin (`role:admin`)

### Dashboard y Estadísticas
```
GET /admin/dashboard          → Dashboard del administrador
GET /admin/statistics         → Estadísticas globales
GET /admin/statistics/export-excel → Exportar trimestral Excel
GET /admin/statistics/export-pdf   → Exportar trimestral PDF
GET /admin/unidades/export-excel   → Exportar por unidades Excel
GET /admin/unidades/export-pdf     → Exportar por unidades PDF
```

### Unidades
```
GET /admin/unidades/                     → Listado de unidades
GET /admin/unidades/{id}/proyectos       → Proyectos de una unidad
GET /admin/unidades/{id}/exportar-poa         → POA Completo Excel
GET /admin/unidades/{id}/exportar-poa-resumido → POA Resumido Excel
```

### Proyectos
```
GET  /admin/proyectos/{id}            → Detalle del proyecto
POST /admin/proyectos/{id}/aprobar    → Aprobar proyecto
POST /admin/proyectos/{id}/rechazar   → Rechazar proyecto
GET  /admin/proyectos/{id}/export-excel → Excel detallado
GET  /admin/proyectos/{id}/export-pdf   → PDF detallado
```

### Panel Administrativo
```
GET|POST|PUT|DELETE /admin/panel/proyectos/{id?}  → CRUD proyectos
POST                /admin/panel/proyectos/{id}/restore → Restaurar
DELETE              /admin/panel/proyectos/trash/empty  → Vaciar papelera

GET|POST|PUT|DELETE /admin/panel/usuarios/{id?}   → CRUD usuarios
GET|POST|PUT|DELETE /admin/panel/unidades/{id?}   → CRUD unidades
GET|POST|PUT|DELETE /admin/panel/metas/{id?}      → CRUD metas
GET|POST|PUT|DELETE /admin/panel/actividades/{id?} → CRUD actividades

GET                 /admin/panel/catalogos          → Ver catálogos
POST|DELETE         /admin/panel/catalogos/metas/{id?}    → CRUD metas predeterminadas
POST|DELETE         /admin/panel/catalogos/objetivos/{id?} → CRUD objetivos predeterminados
```
