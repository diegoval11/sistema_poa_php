<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Unidad;
use App\Models\User;

class UnidadesSeeder extends Seeder
{
    /**
     * Lista de unidades a crear.
     * Formato: [Nombre Real, Alias para usuario]
     */
    private $unidades = [
        ["Sindicatura", "sindicatura"],
        ["Auditoría Interna", "auditoriainterna"],
        ["Secretaría Municipal", "secretariamunicipal"],
        ["UGDA", "ugda"],
        ["UAIP", "uaip"],
        ["DESPACHO MUNICIPAL", "despachomunicipal"],
        ["CAM.", "cam"],
        ["Unidad de Proteccion de Animales de Compañía", "proteccionanimales"],
        ["Desarrollo Comunal.", "desarrollocomunal"],
        ["Unidad de Turismo.", "turismo"],
        ["UCP", "ucp"],
        ["Comunicaciones.", "comunicaciones"],
        ["DIRECCION DE DISTRITO", "direcciondistrito"],
        ["Unidad de Cooperación y Relaciones Internacionales.", "cooperacion"],
        ["Departamento de Talento Humano.", "talentohumano"],
        ["Centro de Contacto de Denuncias y Quejas Ciudadanas.", "contactociudadano"],
        ["Unidad de Gestión de Riesgos.", "gestionriesgos"],
        ["Unidad de Eventos y Festejos Municipales", "eventos"],
        ["Gerencia de Innovación", "innovacion"],
        ["GERENCIA DE PLANIFICACIÓN Y OPERERACIONES", "planificacionoperaciones"],
        ["Transporte y Logística", "transportelogistica"],
        ["GERENCIA FINANCIERA Y TRIBUTARIA", "finanzastributaria"],
        ["Departamento de Presupuesto.", "presupuesto"],
        ["Departamento de Tesorería.", "tesoreria"],
        ["Departamento de Contabilidad.", "contabilidad"],
        ["Departamento de Inventario.", "inventario"],
        ["Unidad de Almacén y Proveeduría.", "almacenproveeduria"],
        ["Sub Gerencia Tributaria", "subgerenciatributaria"],
        ["Departamento de Cuentas Corrientes.", "cuentascorrientes"],
        ["Departamento de Recuperación de Mora.", "recuperacionmora"],
        ["Departamento de Fiscalización.", "fiscalizacion"],
        ["Departamento de Catastro de Inmuebles.", "catastroinmuebles"],
        ["Departamento de Catastro de Empresas", "catastroempresas"],
        ["Unidad de Desarrollo Empresarial.", "desarrolloempresarial"],
        ["GERENCIA TÉCNICA DE DESARROLLO TERRITORIAL", "desarrolloterritorial"],
        ["Departamento de Ingeniería", "ingenieria"],
        ["Unidad de Proyectos", "proyectos"],
        ["Unidad de topografía", "topografia"],
        ["Oficina de Centro Histórico", "centrohistorico"],
        ["Unidad de ornato publico, parques y jardines municipales", "ornatoparques"],
        ["Unidad de Medio Ambiente", "medioambiente"],
        ["GERENCIA DE DESECHOS SOLIDOS", "desechossolidos"],
        ["Aseo Urbano", "aseourbano"],
        ["Recoleccion de Desechos Solidos", "recolecciondesechos"],
        ["Unidad de Orden y Limpieza", "ordenlimpieza"],
        ["GERENCIA DE SERVICIOS DE MANTENIMIENTO.", "serviciosmantenimiento"],
        ["Mtto. de Alumbrado Público y Servicios Generales.", "alumbradopublico"],
        ["Red Vial.", "redvial"],
        ["Monitoreo y mantenimiento de flotas municipales", "monitoreoflotas"],
        ["GERENCIA LEGAL.", "gerencialegal"],
        ["Departamento de Legalización de Bienes Raíces Municipales.", "legalizacionbienes"],
        ["Delegación Contravencional Municipal.", "contravencional"],
        ["Centro de Mediación Municipal.", "mediacion"],
        ["Registro del Estado Familiar y OAT-REF 1 y 2.", "estadofamiliar"],
        ["GERENCIA DE DESARROLLO SOCIAL", "desarrollosocial"],
        ["Sub Gerencia de Desarrollo Social", "subdesarrollosocial"],
        ["Talleres Vocacionales.", "talleresvocacionales"],
        ["Cultura, Arte y Biblioteca Municipal", "culturaartebiblioteca"],
        ["Clínica Municipal.", "clinicamunicipal"],
        ["Recreación y Deportes.", "recreaciondeportes"],
        ["Unidad Municipal de la Mujer.", "mujer"],
        ["Departamento de Impresión Grafica", "impresiongrafica"],
        ["Unidad de la Niñez , Adolescencia y juventud", "ninezjuventud"],
        ["C.B.I Colon", "cbicolon"],
        ["C.B.I Río Zarco", "cbiriozarco"],
        ["GERENCIA DE SERVICIOS MUNICIPALES", "serviciosmunicipales"],
        ["Mercado Municipal 1", "mercado1"],
        ["Mercado Municipal 2 y Terminal de Buses.", "mercado2"],
        ["Mercado Municipal 3", "mercado3"],
        ["Cementerios y Funeraria Municipal.", "cementerios"],
        ["Unidad de prevencion en salud, saneamiento ambental y abastos de los mercados municipales", "prevencionsaludmercados"],
        ["Rastro Municipal.", "rastromunicipal"],
    ];

    public function run()
    {
        $this->command->info('Iniciando carga de Unidades y Usuarios...');

        foreach ($this->unidades as $data) {
            $nombreUnidad = $data[0];
            $alias = $data[1];

            // 1. Crear o buscar la Unidad
            $unidad = Unidad::firstOrCreate(
                ['nombre' => $nombreUnidad], // Criterio de búsqueda
                [
                    'activa' => true,
                    'sin_reporte' => false
                ] // Valores por defecto si se crea
            );

            if ($unidad->wasRecentlyCreated) {
                $this->command->info("  - Creada Unidad: $nombreUnidad");
            } else {
                $this->command->warn("  - Ya existía Unidad: $nombreUnidad");
            }

            // 2. Definir credenciales
            $email = "{$alias}@alcaldia.com";
            
            // Lógica de pass: Primera letra mayúscula + # + 3 letras alias + !2025
            $passString = ucfirst($alias)[0] . '#' . substr($alias, 0, 3) . '!2025';

            // 3. Crear el Usuario si no existe
            $user = User::where('email', $email)->first();

            if (!$user) {
                User::create([
                    'name'      => $nombreUnidad,
                    'email'     => $email,
                    'password'  => Hash::make($passString),
                    'unidad_id' => $unidad->id,
                    'role'      => 'unidad',
                    'debe_cambiar_clave' => true,
                ]);
                
                $this->command->info("    - Creado Usuario: $email (Pass Temp: $passString)");
            } else {
                $this->command->warn("    - Ya existía Usuario: $email");
            }
        }
    }
}
