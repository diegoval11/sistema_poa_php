# Puntos de Extensión

El sistema está diseñado para crecer sin grandes refactorizaciones. Aquí están los puntos de extensión más comunes.

## 1. Agregar una nueva validación al Wizard

**Archivo**: `app/Services/PoaWizardValidator.php`

```php
// Agregar este método
public function validateStep3Extra(PoaProyecto $proyecto): array
{
    // Ejemplo: Verificar que haya al menos un objetivo específico definido
    if (empty($proyecto->objetivo_unidad)) {
        return [
            'allowed' => false,
            'message' => 'Debes definir el objetivo de la unidad.',
            'route'   => 'poa.wizard.step1',
        ];
    }
    return ['allowed' => true];
}
```

Luego llámalo en `WizardController::step3()`:

```php
$check = $this->validator->validateStep3Extra($proyecto);
if (!$check['allowed']) return redirect()->route($check['route'], $id)->with('error', $check['message']);
```

---

## 2. Agregar un nuevo tipo de Evidencia

**Archivo**: `app/Http/Controllers/Poa/AvanceController.php`

```php
// Agregar el nuevo tipo al in:
'tipo' => 'required|string|in:PDF,FOTO,VIDEO,URL,MP3,MP4',
```

También actualiza la vista Blade correspondiente para mostrar el nuevo tipo correctamente.

---

## 3. Agregar un nuevo formato de Exportación

```php
// 1. Crear el servicio
// app/Services/PoaExcelEjecutivoService.php
namespace App\Services;

class PoaExcelEjecutivoService
{
    public function generate($proyecto): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        // construir el archivo aquí
    }
}

// 2. Agregar la ruta en web.php
Route::get('/{id}/exportar-poa-ejecutivo', [UnidadController::class, 'exportPoaEjecutivo'])
     ->name('exportar-poa-ejecutivo');

// 3. Agregar el método en UnidadController
public function exportPoaEjecutivo($id)
{
    $proyecto = PoaProyecto::with(...)->findOrFail($id);
    $service = new PoaExcelEjecutivoService();
    // descargar...
}
```

---

## 4. Agregar un nuevo Rol

```php
// 1. Migración (o modificar la existente)
$table->enum('role', ['admin', 'unidad', 'supervisor'])->default('unidad');

// 2. Grupo de rutas en web.php
Route::middleware(['role:supervisor', 'check.password.change'])
    ->prefix('supervisor')
    ->group(function () {
        // nuevas rutas aquí
    });

// 3. Helper en User.php
public function isSupervisor() { return $this->role === 'supervisor'; }
```

---

## 5. Agregar Notificaciones por Email

Usar el sistema de Notifications de Laravel en los eventos clave:

```php
// En ProyectoAdminController::aprobar()
use App\Notifications\ProyectoAprobadoNotification;

$proyecto->unidad->notify(new ProyectoAprobadoNotification($proyecto));

// En ProyectoAdminController::rechazar()
$proyecto->unidad->notify(new ProyectoRechazadoNotification($proyecto));
```

Crear las clases con `php artisan make:notification ProyectoAprobadoNotification`.

---

## 6. Agregar Catálogos Predeterminados

Sigue el patrón de `MetaPredeterminada`:

```php
// 1. Crear modelo y migración
php artisan make:model NuevoCatalogo -m

// 2. Agregar CRUD en CatalogController
// 3. Agregar rutas en web.php bajo /admin/panel/catalogos/nuevo-catalogo
// 4. Exponer en el wizard como lista de opciones
```
