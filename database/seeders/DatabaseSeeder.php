<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
public function run(): void
{
    // 1. Crear Unidades
    $unidadInnovacion = \App\Models\Unidad::create([
        'nombre' => 'Gerencia de Innovación y Tecnología',
        'activa' => true
    ]);

    // 2. Crear Admin
    \App\Models\User::create([
        'name' => 'Admin Alcaldía',
        'email' => 'admin@alcaldia.gob.sv',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'debe_cambiar_clave' => false,
    ]);

    \App\Models\User::create([
        'name' => 'Jefe de Innovación',
        'email' => 'innovacion@alcaldia.gob.sv',
        'password' => bcrypt('password'),
        'role' => 'unidad',
        'unidad_id' => $unidadInnovacion->id,
        'debe_cambiar_clave' => true,
    ]);
}
}
