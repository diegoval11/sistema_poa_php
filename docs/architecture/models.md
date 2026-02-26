# Modelos y Base de Datos

## Diagrama de Entidad-Relación

```mermaid
erDiagram
    UNIDADES {
        int id PK
        string nombre
        boolean activa
        boolean sin_reporte
        datetime created_at
        datetime updated_at
    }
    USERS {
        int id PK
        int unidad_id FK
        string name
        string email
        string password
        string role
        boolean debe_cambiar_clave
        datetime created_at
        datetime updated_at
    }
    POA_PROYECTOS {
        int id PK
        int user_id FK
        int aprobado_por FK
        string nombre
        year anio
        text objetivo_unidad
        enum estado
        datetime fecha_aprobacion
        text motivo_rechazo
        datetime deleted_at
        datetime created_at
        datetime updated_at
    }
    POA_METAS {
        int id PK
        int poa_proyecto_id FK
        text descripcion
        datetime created_at
        datetime updated_at
    }
    POA_ACTIVIDADES {
        int id PK
        int poa_meta_id FK
        string descripcion
        string unidad_medida
        boolean es_cuantificable
        boolean es_no_planificada
        enum estado_aprobacion
        int cantidad_programada_total
        text medio_verificacion
        decimal costo_estimado
        datetime created_at
        datetime updated_at
    }
    POA_PROGRAMACIONES {
        int id PK
        int poa_actividad_id FK
        int mes
        year anio
        int cantidad_programada
        int cantidad_ejecutada
        text causal_desvio
        boolean es_extraordinaria
        datetime created_at
        datetime updated_at
    }
    POA_EVIDENCIAS {
        int id PK
        int poa_actividad_id FK
        string tipo
        string archivo
        string url
        string descripcion
        int mes
        datetime created_at
        datetime updated_at
    }

    UNIDADES ||--o{ USERS : "tiene"
    USERS ||--o{ POA_PROYECTOS : "crea"
    POA_PROYECTOS ||--|{ POA_METAS : "contiene"
    POA_METAS ||--|{ POA_ACTIVIDADES : "agrupa"
    POA_ACTIVIDADES ||--|{ POA_PROGRAMACIONES : "programa"
    POA_ACTIVIDADES ||--o{ POA_EVIDENCIAS : "adjunta"
```

## Descripción de Modelos

### `PoaActividad` — El modelo central

Es el corazón del sistema. Tiene un observer `booted()` que al crearse **genera automáticamente 12 registros** de `PoaProgramacion` (uno por cada mes del año):

```php
protected static function booted()
{
    static::created(function ($actividad) {
        $anio = $actividad->meta->proyecto->anio ?? date('Y');
        $batch = [];
        for ($i = 1; $i <= 12; $i++) {
            $batch[] = [
                'poa_actividad_id'  => $actividad->id,
                'mes'               => $i,
                'anio'              => $anio,
                'cantidad_programada' => 0,
            ];
        }
        PoaProgramacion::insert($batch);
    });
}
```

### Enums importantes

| Modelo | Campo | Valores |
|---|---|---|
| `PoaProyecto` | `estado` | `BORRADOR`, `ENVIADO`, `APROBADO`, `RECHAZADO`, `OBSERVADO` |
| `PoaActividad` | `estado_aprobacion` | `PENDIENTE`, `APROBADO`, `RECHAZADO` |
| `PoaEvidencia` | `tipo` | `PDF`, `FOTO`, `VIDEO`, `URL`, `MP3` |

### Constraint único en `PoaProgramacion`

```sql
UNIQUE(poa_actividad_id, mes, anio)
```
Evita duplicados de programación por actividad/mes/año.

### SoftDeletes en `PoaProyecto`

Los proyectos no se eliminan físicamente inmediatamente — van a la papelera (`deleted_at`). El Admin puede restaurarlos desde el panel.
