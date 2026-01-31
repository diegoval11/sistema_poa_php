<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POA Detallado - {{ $proyecto->nombre }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1E40AF;
        }
        
        .title {
            font-size: 18pt;
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .info-line {
            font-size: 9pt;
            color: #6B7280;
            margin-bottom: 2px;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1E40AF;
            margin-top: 15px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 2px solid #1E40AF;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        table.info-table th {
            background-color: #E0E7FF;
            color: #1E40AF;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #D1D5DB;
            width: 35%;
        }
        
        table.info-table td {
            padding: 6px 8px;
            border: 1px solid #D1D5DB;
        }
        
        table.data-table thead th {
            background-color: #1E40AF;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #1E40AF;
        }
        
        table.data-table tbody td {
            padding: 5px 4px;
            border: 1px solid #D1D5DB;
            text-align: center;
        }
        
        table.data-table tbody td.left {
            text-align: left;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .meta-header {
            background-color: #1E40AF;
            color: white;
            font-weight: bold;
            padding: 8px;
            margin-top: 12px;
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .activity-subtitle {
            font-size: 9pt;
            font-style: italic;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
            color: #4B5563;
        }
        
        .evidence-subtitle {
            font-size: 9pt;
            font-style: italic;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
            color: #059669;
        }
        
        table.evidence-table thead th {
            background-color: #059669;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 5px 4px;
            border: 1px solid #059669;
        }
        
        .estado-excelente {
            color: #059669;
            font-weight: bold;
        }
        
        .estado-deficiente {
            color: #DC2626;
            font-weight: bold;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #9CA3AF;
            padding-top: 5px;
            border-top: 1px solid #E5E7EB;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="title">PLAN OPERATIVO ANUAL (POA)</div>
        <div class="subtitle">{{ $proyecto->nombre }}</div>
        <div class="info-line">Unidad Responsable: {{ $proyecto->unidad->unidad->nombre }}</div>
        <div class="info-line">Año de Ejecución: {{ $proyecto->anio }}</div>
        <div class="info-line">Estado: {{ $proyecto->estado }}</div>
        <div class="info-line">Fecha de Generación: {{ $fechaGeneracion }}</div>
    </div>

    {{-- Información General --}}
    <div class="section-title">INFORMACIÓN GENERAL</div>
    <table class="info-table">
        <tr>
            <th>Objetivo de la Unidad:</th>
            <td>{{ $proyecto->objetivo_unidad ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Fecha de Creación:</th>
            <td>{{ $proyecto->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Última Modificación:</th>
            <td>{{ $proyecto->updated_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if($proyecto->estado === 'APROBADO' && $proyecto->aprobado_por)
        @php
            $aprobador = \App\Models\User::find($proyecto->aprobado_por);
        @endphp
        <tr>
            <th>Aprobado por:</th>
            <td>{{ $aprobador ? $aprobador->email : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Fecha de Aprobación:</th>
            <td>{{ $proyecto->fecha_aprobacion ? $proyecto->fecha_aprobacion->format('d/m/Y H:i') : 'N/A' }}</td>
        </tr>
        @endif
    </table>

    {{-- Resumen Ejecutivo --}}
    <div class="section-title">RESUMEN EJECUTIVO</div>
    <table class="info-table">
        <tr>
            <th>Total de Metas:</th>
            <td>{{ $totalMetas }}</td>
        </tr>
        <tr>
            <th>Total de Actividades:</th>
            <td>{{ $totalActividades }}</td>
        </tr>
        <tr>
            <th>Presupuesto Total:</th>
            <td>${{ number_format($presupuestoTotal, 2) }}</td>
        </tr>
        <tr>
            <th>Total de Evidencias:</th>
            <td>{{ $totalEvidencias }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- Metas y Actividades Detalladas --}}
    <div class="section-title">METAS Y ACTIVIDADES DETALLADAS</div>

    @foreach($proyecto->metas as $metaIndex => $meta)
        <div class="meta-header">META {{ $metaIndex + 1 }}: {{ $meta->descripcion }}</div>

        {{-- Activities Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 45%">Actividad</th>
                    <th style="width: 10%">U.M.</th>
                    <th style="width: 10%">Cant.</th>
                    <th style="width: 15%">Recursos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meta->actividades as $actIndex => $actividad)
                <tr>
                    <td>{{ $actIndex + 1 }}</td>
                    <td class="left">{{ $actividad->descripcion }}</td>
                    <td>{{ $actividad->unidad_medida }}</td>
                    <td>{{ $actividad->cantidad_programada_total }}</td>
                    <td>${{ number_format($actividad->costo_estimado, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Programming and Evidence for each activity --}}
        @foreach($meta->actividades as $actIndex => $actividad)
            <div class="activity-subtitle">Programación: {{ \Illuminate\Support\Str::limit($actividad->descripcion, 60) }}...</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%">Mes</th>
                        <th style="width: 15%">Prog.</th>
                        <th style="width: 15%">Real.</th>
                        <th style="width: 15%">%</th>
                        <th style="width: 20%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $mesesNombres = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    @endphp
                    @foreach($actividad->programaciones->sortBy('mes') as $prog)
                    @php
                        $cumplimiento = 0;
                        $estado = 'Deficiente';
                        $estadoClass = 'estado-deficiente';
                        
                        if ($prog->cantidad_programada > 0) {
                            $cumplimiento = round(($prog->cantidad_ejecutada / $prog->cantidad_programada) * 100, 2);
                            if ($cumplimiento >= 100) {
                                $estado = 'Excelente';
                                $estadoClass = 'estado-excelente';
                            } elseif ($cumplimiento >= 50) {
                                $estado = 'Bueno';
                                $estadoClass = '';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $mesesNombres[$prog->mes] }}</td>
                        <td>{{ $prog->cantidad_programada }}</td>
                        <td>{{ $prog->cantidad_ejecutada ?? 0 }}</td>
                        <td>{{ $cumplimiento }}%</td>
                        <td class="{{ $estadoClass }}">{{ $estado }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Evidences --}}
            @if($actividad->evidencias->count() > 0)
            <div class="evidence-subtitle">Evidencias ({{ $actividad->evidencias->count() }})</div>
            <table class="evidence-table">
                <thead>
                    <tr>
                        <th style="width: 10%">Tipo</th>
                        <th style="width: 10%">Mes</th>
                        <th style="width: 60%">Descripción</th>
                        <th style="width: 20%">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actividad->evidencias as $evidencia)
                    @php
                        $tipo = 'PDF';
                        if ($evidencia->archivo) {
                            $extension = pathinfo($evidencia->archivo, PATHINFO_EXTENSION);
                            $tipo = strtoupper($extension);
                        }
                        
                        $mes = $evidencia->mes ? $mesesNombres[$evidencia->mes] : 'N/A';
                    @endphp
                    <tr>
                        <td>{{ $tipo }}</td>
                        <td>{{ $mes }}</td>
                        <td class="left">{{ $evidencia->descripcion ?? 'Sin descripción' }}</td>
                        <td>{{ $evidencia->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        @endforeach

        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
