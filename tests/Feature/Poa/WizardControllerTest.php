<?php

use App\Models\User;
use App\Models\PoaProyecto;
use App\Models\PoaMeta;
use App\Models\PoaActividad;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'unidad']);
});

test('user can create proyecto with valid data', function () {
    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeStep1'), [
        'anio' => 2026,
        'nombre' => 'Proyecto Test',
        'objetivo_unidad' => 'Objetivo de prueba',
    ]);

    $response->assertRedirect();
    expect($this->user->id)->toBeInt();
    
    $this->assertDatabaseHas('poa_proyectos', [
        'user_id' => $this->user->id,
        'anio' => 2026,
        'nombre' => 'Proyecto Test',
        'estado' => 'BORRADOR',
    ]);
});

test('proyecto requires anio', function () {
    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeStep1'), [
        'nombre' => 'Proyecto Sin Año',
    ]);

    $response->assertSessionHasErrors(['anio']);
});

test('user can add meta to proyecto', function () {
    $proyecto = PoaProyecto::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeMeta', $proyecto->id), [
        'meta_predeterminada' => 'Fortalecimiento Institucional',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('poa_metas', [
        'poa_proyecto_id' => $proyecto->id,
        'descripcion' => 'Fortalecimiento Institucional',
    ]);
});

test('actividad requires integer cantidad programada', function () {
    $proyecto = PoaProyecto::factory()->create(['user_id' => $this->user->id]);
    $meta = PoaMeta::factory()->create(['poa_proyecto_id' => $proyecto->id]);

    // CRÍTICO: Intentar enviar un decimal debe fallar
    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeActividad', $proyecto->id), [
        'poa_meta_id' => $meta->id,
        'descripcion' => 'Actividad Test',
        'unidad_medida' => 'Informe',
        'es_cuantificable' => '1',
        'cantidad_programada_total' => '10.5', // Decimal NO permitido
        'medio_verificacion' => 'Listado',
        'costo_estimado' => 1000,
    ]);

    $response->assertSessionHasErrors(['cantidad_programada_total']);
});

test('actividad accepts integer cantidad programada', function () {
    $proyecto = PoaProyecto::factory()->create(['user_id' => $this->user->id]);
    $meta = PoaMeta::factory()->create(['poa_proyecto_id' => $proyecto->id]);

    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeActividad', $proyecto->id), [
        'poa_meta_id' => $meta->id,
        'descripcion' => 'Actividad Test',
        'unidad_medida' => 'Informe',
        'es_cuantificable' => '1',
        'cantidad_programada_total' => '10', // Entero válido
        'medio_verificacion' => 'Listado',
        'costo_estimado' => 1000,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('poa_actividades', [
        'poa_meta_id' => $meta->id,
        'descripcion' => 'Actividad Test',
        'cantidad_programada_total' => 10,
    ]);

    // Verificar que se crearon 12 programaciones
    $actividad = PoaActividad::where('descripcion', 'Actividad Test')->first();
    expect($actividad->programaciones)->toHaveCount(12);
});

test('programacion mensual validates sum equals total', function () {
    $proyecto = PoaProyecto::factory()->create(['user_id' => $this->user->id]);
    $meta = PoaMeta::factory()->create(['poa_proyecto_id' => $proyecto->id]);
    $actividad = PoaActividad::factory()->create([
        'poa_meta_id' => $meta->id,
        'cantidad_programada_total' => 100,
        'es_cuantificable' => true,
    ]);

    // Distribuir de manera INCORRECTA (suma = 99, no 100)
    $programacion = [];
    foreach ($actividad->programaciones as $prog) {
        $programacion[$actividad->id][$prog->mes] = $prog->mes <= 11 ? 9 : 0; // Suma = 99
    }

    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeProgramacion', $proyecto->id), [
        'programacion' => $programacion,
    ]);

    $response->assertRedirect(route('poa.wizard.step4', $proyecto->id));
    $response->assertSessionHas('error');
});

test('programacion mensual accepts correct distribution', function () {
    $proyecto = PoaProyecto::factory()->create(['user_id' => $this->user->id]);
    $meta = PoaMeta::factory()->create(['poa_proyecto_id' => $proyecto->id]);
    $actividad = PoaActividad::factory()->create([
        'poa_meta_id' => $meta->id,
        'cantidad_programada_total' => 120,
        'es_cuantificable' => true,
    ]);

    // Distribuir correctamente (10 por mes)
    $programacion = [];
    foreach ($actividad->programaciones as $prog) {
        $programacion[$actividad->id][$prog->mes] = 10;
    }

    $response = $this->actingAs($this->user)->post(route('poa.wizard.storeProgramacion', $proyecto->id), [
        'programacion' => $programacion,
    ]);

    $response->assertRedirect(route('poa.wizard.step5', $proyecto->id));
    $this->assertDatabaseHas('poa_programaciones', [
        'poa_actividad_id' => $actividad->id,
        'mes' => 1,
        'cantidad_programada' => 10,
    ]);
});

test('user cannot delete proyecto in enviado status', function () {
    $proyecto = PoaProyecto::factory()->create([
        'user_id' => $this->user->id,
        'estado' => 'ENVIADO',
    ]);

    $response = $this->actingAs($this->user)->post(route('poa.wizard.deleteProject', $proyecto->id));

    $response->assertStatus(404);
    $this->assertDatabaseHas('poa_proyectos', ['id' => $proyecto->id]);
});

test('user can delete proyecto in borrador status', function () {
    $proyecto = PoaProyecto::factory()->create([
        'user_id' => $this->user->id,
        'estado' => 'BORRADOR',
    ]);

    $response = $this->actingAs($this->user)->post(route('poa.wizard.deleteProject', $proyecto->id));

    $response->assertRedirect();
    $this->assertSoftDeleted('poa_proyectos', ['id' => $proyecto->id]);
});
