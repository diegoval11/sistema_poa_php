# Servicios

Los servicios encapsulan lógica de negocio reutilizable, separándola de los controladores.

## `PoaWizardValidator`

Inyectado vía constructor en `WizardController`. Valida el estado del proyecto antes de avanzar pasos.

```php
use App\Services\PoaWizardValidator;

class WizardController extends Controller
{
    public function __construct(PoaWizardValidator $validator)
    {
        $this->validator = $validator;
    }
}
```

### Métodos disponibles

| Método | Retorna | Descripción |
|---|---|---|
| `validateStep3($proyecto)` | `array{allowed, message?, route?}` | Verifica que existan metas |
| `validateStep4($proyecto)` | `array{allowed, ...}` | Verifica que todas las metas tengan actividades |
| `validateStep5($proyecto)` | `array{allowed, ...}` | Verifica distribución matemática |
| `checkDistribution($proyecto)` | `array{valid, message?}` | Suma mensual == total anual por actividad |

---

## `PoaExcelService`

Genera el reporte **"POA Completo"** con fórmulas Excel.

```php
// Uso desde UnidadController
$service = new PoaExcelService();
$spreadsheet = $service->generate($proyecto);
```

Características:
- Usa una plantilla `.xlsx` base
- Escribe filas dinámicas por actividad y mes
- Agrega fórmulas Excel para cálculo de cumplimiento trimestral/anual
- Maneja actividades planificadas y no planificadas con filas separadas

---

## `PoaExcelResumidoService`

Genera la versión condensada del POA.

```php
$service = new PoaExcelResumidoService();
$spreadsheet = $service->generate($proyecto);
```

Características:
- Formato más compacto
- Incluye columna de recursos totales por actividad
- Celdas fusionadas para encabezados jerárquicos

---

## Agregar un Nuevo Servicio

Para seguir el patrón del proyecto al crear un nuevo servicio:

```php
// app/Services/MiNuevoService.php
namespace App\Services;

class MiNuevoService
{
    public function procesar($data): array
    {
        // lógica aquí
        return ['resultado' => '...'];
    }
}
```

Luego inyéctalo en el controlador:

```php
public function __construct(MiNuevoService $servicio)
{
    $this->servicio = $servicio;
}
```

::: tip
Laravel resuelve automáticamente las dependencias del constructor (Dependency Injection) sin necesidad de registrar los servicios en ningún provider, siempre que no tengan dependencias externas no tipadas.
:::
