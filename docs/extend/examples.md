# Ejemplos de Uso

Snippets listos para copiar que muestran cómo interactuar con los modelos y servicios del sistema.

## Crear un proyecto completo desde cero

```php
use App\Models\PoaProyecto;
use App\Models\PoaMeta;
use App\Models\PoaActividad;
use App\Models\PoaProgramacion;
use App\Services\PoaWizardValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

DB::transaction(function () {

    // Paso 1: Crear el proyecto
    $proyecto = PoaProyecto::create([
        'user_id'          => Auth::id(),
        'anio'             => 2026,
        'nombre'           => 'Plan de Infraestructura Vial',
        'objetivo_unidad'  => 'Mejorar la infraestructura vial del municipio',
        'estado'           => 'BORRADOR',
    ]);

    // Paso 2: Agregar una meta
    $meta = PoaMeta::create([
        'poa_proyecto_id' => $proyecto->id,
        'descripcion'     => 'Mantenimiento de calles pavimentadas',
    ]);

    // Paso 3: Agregar una actividad
    // ⚡ Esto crea automáticamente 12 PoaProgramacion (una por mes, todas en 0)
    $actividad = PoaActividad::create([
        'poa_meta_id'               => $meta->id,
        'descripcion'               => 'Reparación de baches zona norte',
        'unidad_medida'             => 'Metro cuadrado',
        'es_cuantificable'          => true,
        'cantidad_programada_total' => 500,
        'medio_verificacion'        => 'Informe fotográfico mensual',
        'costo_estimado'            => 15000.00,
    ]);

    // Paso 4: Distribuir la programación mensual
    $distribucion = [1 => 50, 2 => 50, 3 => 50, 4 => 50,
                     5 => 50, 6 => 50, 7 => 50, 8 => 50,
                     9 => 50, 10 => 50, 11 => 50, 12 => 50];

    foreach ($distribucion as $mes => $cantidad) {
        PoaProgramacion::where('poa_actividad_id', $actividad->id)
            ->where('mes', $mes)
            ->update(['cantidad_programada' => $cantidad]);
    }

    // Validar distribución
    $validator = new PoaWizardValidator();
    $proyecto->load('metas.actividades.programaciones');
    $check = $validator->checkDistribution($proyecto);

    if (!$check['valid']) {
        throw new \Exception($check['message']);
    }
});
```

---

## Registrar un Avance de Ejecución

```php
use App\Models\PoaProgramacion;

// Registrar que en enero se ejecutaron 45 de 50 programados
PoaProgramacion::where('poa_actividad_id', $actividadId)
    ->where('mes', 1)
    ->update(['cantidad_ejecutada' => 45]);

// Registrar causal cuando no se pudo ejecutar en febrero
PoaProgramacion::where('poa_actividad_id', $actividadId)
    ->where('mes', 2)
    ->update([
        'cantidad_ejecutada' => 0,
        'causal_desvio'      => 'Demora en la aprobación de presupuesto municipal',
    ]);
```

---

## Calcular Cumplimiento de una Actividad

```php
$actividad = PoaActividad::with('programaciones')->find($actividadId);

$totalProgramado = 0;
$totalEjecutado  = 0;

foreach ($actividad->programaciones as $prog) {
    $p = (float) $prog->cantidad_programada;
    $e = (float) $prog->cantidad_ejecutada;

    if ($p > 0) {
        $totalProgramado += $p;
        $totalEjecutado  += min($p, $e); // Cap al 100%
    }
}

$cumplimiento = $totalProgramado > 0
    ? round(($totalEjecutado / $totalProgramado) * 100, 1)
    : 0;

echo "Cumplimiento: {$cumplimiento}%";
```

---

## Usar el Validador del Wizard

```php
use App\Services\PoaWizardValidator;
use App\Models\PoaProyecto;

$proyecto  = PoaProyecto::with('metas.actividades.programaciones')->findOrFail($id);
$validator = new PoaWizardValidator();

// Verificar si puede ingresar al paso 5
$result = $validator->validateStep5($proyecto);

if (!$result['allowed']) {
    // Redirigir al paso indicado con el mensaje de error
    return redirect()->route($result['route'], $id)->with('error', $result['message']);
}
```

---

## Consultas Frecuentes con Eloquent

```php
// Todos los proyectos aprobados de una unidad
PoaProyecto::where('user_id', $userId)
    ->where('estado', 'APROBADO')
    ->with('metas.actividades.programaciones')
    ->get();

// Actividades con ejecución incompleta en un mes específico
PoaProgramacion::where('mes', 3)
    ->whereColumn('cantidad_ejecutada', '<', 'cantidad_programada')
    ->where('cantidad_programada', '>', 0)
    ->with('actividad.meta.proyecto')
    ->get();

// Presupuesto total de un proyecto
PoaActividad::whereHas('meta', fn($q) => $q->where('poa_proyecto_id', $proyectoId))
    ->sum('costo_estimado');
```
