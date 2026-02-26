# Referencia de Controladores

## Zona de Unidad (`App\Http\Controllers\Poa`)

### `PoaController`
| Método | Ruta | Descripción |
|---|---|---|
| `index()` | `GET /poa/mis-proyectos` | Lista todos los proyectos del usuario autenticado |

---

### `WizardController`
| Método | Ruta | Descripción |
|---|---|---|
| `step1($id?)` | `GET /poa/wizard/inicio/{id?}` | Vista del paso 1 (datos del proyecto) |
| `storeStep1($id?)` | `POST /poa/wizard/inicio/{id?}` | Crea o actualiza el proyecto |
| `step2($id)` | `GET /poa/wizard/{id}/metas` | Vista del paso 2 (metas) |
| `storeMeta($id)` | `POST /poa/wizard/{id}/metas` | Agrega una meta |
| `updateMeta($id)` | `PUT /poa/wizard/actualizar-meta/{id}` | Edita una meta (solo BORRADOR) |
| `deleteMeta($id)` | `DELETE /poa/wizard/eliminar-meta/{id}` | Elimina una meta |
| `step3($id)` | `GET /poa/wizard/{id}/actividades` | Vista del paso 3 (actividades) |
| `storeActividad($id)` | `POST /poa/wizard/{id}/actividades` | Agrega una actividad |
| `updateActividad($id)` | `PUT /poa/wizard/actualizar-actividad/{id}` | Edita una actividad (solo BORRADOR) |
| `deleteActividad($id)` | `DELETE /poa/wizard/eliminar-actividad/{id}` | Elimina una actividad |
| `step4($id)` | `GET /poa/wizard/{id}/programacion` | Vista del paso 4 (cronograma) |
| `updateProgramacion($id)` | `POST /poa/wizard/{id}/programacion` | Guarda el cronograma mensual |
| `step5($id)` | `GET /poa/wizard/{id}/resumen` | Vista del paso 5 (resumen) |
| `finish($id)` | `POST /poa/wizard/{id}/finalizar` | Finaliza el wizard |
| `sendProject($id)` | `POST /poa/wizard/{id}/enviar` | Cambia estado a ENVIADO |
| `deleteProject($id)` | `POST /poa/wizard/{id}/eliminar` | Elimina el proyecto (solo BORRADOR/RECHAZADO) |

---

### `AvanceController`
| Método | Ruta | Descripción |
|---|---|---|
| `index($proyectoId)` | `GET /poa/avances/{proyectoId}` | Vista de avances con estadísticas |
| `update($request)` | `POST /poa/avances/update` | Actualiza `cantidad_ejecutada` |
| `updateCausal($request)` | `POST /poa/avances/update-causal` | Guarda causal de incumplimiento |
| `storeEvidencia($request)` | `POST /poa/avances/store-evidencia` | Sube una evidencia |
| `getEvidencias($actividadId, $mes)` | `GET /poa/avances/evidencias-mes/{actividadId}/{mes}` | Retorna evidencias en JSON |

---

## Zona de Admin (`App\Http\Controllers\Admin`)

### `ProyectoAdminController`
| Método | Ruta | Descripción |
|---|---|---|
| `detalle($id)` | `GET /admin/proyectos/{id}` | Detalle con métricas del proyecto |
| `aprobar($id)` | `POST /admin/proyectos/{id}/aprobar` | Aprueba un proyecto ENVIADO |
| `rechazar($id)` | `POST /admin/proyectos/{id}/rechazar` | Rechaza con motivo obligatorio |
| `exportExcel($id)` | `GET /admin/proyectos/{id}/export-excel` | Descarga Excel detallado |
| `exportPdf($id)` | `GET /admin/proyectos/{id}/export-pdf` | Descarga PDF detallado |

### `AdminPanelController`
CRUD completo (`index`, `store`, `update`, `destroy`) para: proyectos, usuarios, unidades, metas y actividades. Además: `restoreProyecto`, `emptyTrash`.

### `UnidadController`
| Método | Rutas |
|---|---|
| `index()` | `GET /admin/unidades/` |
| `proyectos($id)` | `GET /admin/unidades/{id}/proyectos` |
| `exportPoaExcel($id)` | `GET /admin/unidades/{id}/exportar-poa` |
| `exportPoaExcelResumido($id)` | `GET /admin/unidades/{id}/exportar-poa-resumido` |
