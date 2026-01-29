<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cumplimiento Trimestral</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
            color: #1f2937;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            padding: 15px;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: white;
            letter-spacing: 0.5px;
        }
        
        .metadata {
            background-color: #dbeafe;
            border: 2px solid #7c3aed;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .metadata-row {
            display: table;
            width: 100%;
            margin: 4px 0;
        }
        
        .metadata-label {
            display: table-cell;
            font-weight: bold;
            color: #1e40af;
            width: 140px;
        }
        
        .metadata-value {
            display: table-cell;
            color: #374151;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 20px 0 12px 0;
            color: #1e40af;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            border: 1px solid #1e40af;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }
        
        table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 9px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        table tbody tr:hover {
            background-color: #eff6ff;
        }
        
        table td:first-child {
            text-align: center;
            width: 35px;
            font-weight: bold;
            color: #6b7280;
        }
        
        table td:nth-child(2) {
            text-align: left;
            padding-left: 10px;
        }
        
        table td:nth-child(3),
        table td:nth-child(4),
        table td:nth-child(5),
        table td:nth-child(6) {
            text-align: center;
            width: 70px;
            font-weight: 600;
        }
        
        .footer {
            margin-top: 20px;
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            font-weight: bold;
            font-size: 11px;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
        }
        
        .performance-high {
            color: #059669;
            font-weight: bold;
        }
        
        .performance-medium {
            color: #d97706;
            font-weight: bold;
        }
        
        .performance-low {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE CUMPLIMIENTO TRIMESTRAL</h1>
    </div>
    
    <div class="metadata">
        <div class="metadata-row">
            <span class="metadata-label">Fecha de Generación:</span>
            <span class="metadata-value">{{ $fechaGeneracion }}</span>
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Generado por:</span>
            <span class="metadata-value">{{ $usuarioGenerador }}</span>
        </div>
    </div>
    
    <div class="section-title">DETALLE POR UNIDAD</div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Unidad</th>
                <th>Trimestre 1</th>
                <th>Trimestre 2</th>
                <th>Trimestre 3</th>
                <th>Trimestre 4</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unidades as $index => $unidad)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $unidad->unidad->nombre ?? $unidad->name }}</td>
                <td class="@if($unidad->quarters[1] >= 80) performance-high @elseif($unidad->quarters[1] >= 60) performance-medium @else performance-low @endif">
                    {{ $unidad->quarters[1] }}%
                </td>
                <td class="@if($unidad->quarters[2] >= 80) performance-high @elseif($unidad->quarters[2] >= 60) performance-medium @else performance-low @endif">
                    {{ $unidad->quarters[2] }}%
                </td>
                <td class="@if($unidad->quarters[3] >= 80) performance-high @elseif($unidad->quarters[3] >= 60) performance-medium @else performance-low @endif">
                    {{ $unidad->quarters[3] }}%
                </td>
                <td class="@if($unidad->quarters[4] >= 80) performance-high @elseif($unidad->quarters[4] >= 60) performance-medium @else performance-low @endif">
                    {{ $unidad->quarters[4] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Total de unidades reportadas: {{ $unidades->count() }}
    </div>
</body>
</html>

