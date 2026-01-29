<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Unidades y Cumplimiento</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            margin: 15px;
            color: #1f2937;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            background-color: #1e40af;
            padding: 12px;
            border: 2px solid #1e3a8a;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        
        .metadata {
            text-align: left;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            color: #1e40af;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 4px;
        }
        
        /* Summary table */
        .summary-table {
            width: 60%;
            margin: 0 auto 20px auto;
            border-collapse: collapse;
        }
        
        .summary-table th {
            background-color: #1e40af;
            color: #ffffff;
            border: 1px solid #1e3a8a;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }
        
        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 5px;
            font-size: 8px;
            text-align: center;
        }
        
        .summary-table tbody tr:nth-child(1) {
            background-color: #e0e7ff;
            font-weight: bold;
        }
        
        .summary-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .summary-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        /* Detail table */
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table.detail-table th {
            background-color: #1e40af;
            color: #ffffff;
            border: 1px solid #1e3a8a;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
        }
        
        table.detail-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            font-size: 7px;
        }
        
        table.detail-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table.detail-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        table.detail-table td:first-child {
            text-align: center;
            width: 25px;
            font-weight: bold;
            color: #6b7280;
        }
        
        table.detail-table td:nth-child(2) {
            text-align: left;
            padding-left: 6px;
            width: 140px;
        }
        
        table.detail-table td:nth-child(3) {
            text-align: left;
            padding-left: 6px;
            font-size: 7px;
            width: 120px;
        }
        
        table.detail-table td:nth-child(4),
        table.detail-table td:nth-child(5),
        table.detail-table td:nth-child(6) {
            text-align: center;
            width: 45px;
            font-weight: 600;
        }
        
        table.detail-table td:nth-child(7) {
            text-align: center;
            width: 60px;
            font-weight: bold;
            font-size: 8px;
        }
        
        .categoria-excelente {
            background-color: #059669 !important;
            color: white !important;
        }
        
        .categoria-bueno {
            background-color: #eab308 !important;
            color: white !important;
        }
        
        .categoria-regular {
            background-color: #f97316 !important;
            color: white !important;
        }
        
        .categoria-bajo {
            background-color: #fecaca !important;
            color: #dc2626 !important;
        }
        
        .footer {
            margin-top: 10px;
            font-size: 7px;
            text-align: center;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE UNIDADES Y CUMPLIMIENTO</h1>
    </div>
    
    <div class="metadata">
        <strong>Fecha de Generación:</strong> {{ $fechaGeneracion }}
    </div>
    
    <div class="section-title">RESUMEN GENERAL</div>
    
    <table class="summary-table">
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total de Unidades</td>
                <td>{{ $resumen['total'] }}</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>Excelente (≥80%)</td>
                <td>{{ $resumen['excelente'] }}</td>
                <td>{{ $resumen['total'] > 0 ? number_format(($resumen['excelente'] / $resumen['total']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>Bueno (60-79%)</td>
                <td>{{ $resumen['bueno'] }}</td>
                <td>{{ $resumen['total'] > 0 ? number_format(($resumen['bueno'] / $resumen['total']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>Regular (40-59%)</td>
                <td>{{ $resumen['regular'] }}</td>
                <td>{{ $resumen['total'] > 0 ? number_format(($resumen['regular'] / $resumen['total']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>Bajo (<40%)</td>
                <td>{{ $resumen['bajo'] }}</td>
                <td>{{ $resumen['total'] > 0 ? number_format(($resumen['bajo'] / $resumen['total']) * 100, 1) : 0 }}%</td>
            </tr>
        </tbody>
    </table>
    
    <div class="section-title">DETALLE POR UNIDAD</div>
    
    <table class="detail-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Unidad</th>
                <th>Email</th>
                <th>Total<br>Proyectos</th>
                <th>Aprobados</th>
                <th>Rendimiento</th>
                <th>Categoría</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unidades as $index => $unidad)
            @php
                $categoriaClass = 'categoria-bajo';
                $categoriaTexto = 'Bajo';
                
                if ($unidad->rendimiento >= 80) {
                    $categoriaClass = 'categoria-excelente';
                    $categoriaTexto = 'Excelente';
                } elseif ($unidad->rendimiento >= 60) {
                    $categoriaClass = 'categoria-bueno';
                    $categoriaTexto = 'Bueno';
                } elseif ($unidad->rendimiento >= 40) {
                    $categoriaClass = 'categoria-regular';
                    $categoriaTexto = 'Regular';
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $unidad->unidad->nombre ?? $unidad->name }}</td>
                <td>{{ $unidad->email }}</td>
                <td>{{ $unidad->total_proyectos }}</td>
                <td>{{ $unidad->count_proyectos_aprobados }}</td>
                <td>{{ $unidad->rendimiento }}%</td>
                <td class="{{ $categoriaClass }}">{{ $categoriaTexto }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Documento generado automáticamente el {{ $fechaGeneracion }}<br>
        Sistema de Gestión POA - Alcaldía
    </div>
</body>
</html>
