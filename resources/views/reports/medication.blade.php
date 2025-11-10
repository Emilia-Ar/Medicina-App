<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Tomas - {{ $medication->name }}</title>
    
    <style>
        /* --- Estilos Generales --- */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #333; 
            line-height: 1.4;
            font-size: 14px;
        }
        @page {
            margin: 130px 50px 80px 50px; 
        }

        /* --- Encabezado y Pie de Página Fijos --- */
        header { 
            position: fixed; 
            top: -100px;
            left: 0; 
            right: 0; 
            height: 90px;
            padding: 10px 50px;
            border-bottom: 2px solid #007bff;
        }
        footer {
            position: fixed; 
            bottom: -60px;
            left: 0; 
            right: 0; 
            height: 50px;
            font-size: 12px;
            text-align: center;
            color: #888;
        }
        footer .page-number:before {
            content: "Página " counter(page);
        }

        /* --- Logo y Título en el Encabezado --- */
        .logo {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 20px;
        }
        .header-title { float: left; }
        .header-title h1 { margin: 0; color: #333; font-size: 28px; }
        .header-title p { margin: 5px 0 0 0; font-size: 16px; color: #555; }

        /* --- Tarjetas de Resumen (Stats) --- */
        .stats-container {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .stat-card {
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            background-color: #f8f8f8;
            border: 1px solid #eee;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }
        .stat-card p {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #777;
        }
        .color-blue { color: #007bff; }
        .color-green { color: #28a745; }
        .color-red { color: #dc3545; }

        /* --- Barra de Estadística --- */
        .stat-bar-container {
            width: 100%;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
            margin-bottom: 30px;
            height: 30px;
        }
        .stat-bar-inner {
            height: 30px;
            line-height: 30px;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-radius: 4px;
        }
        .stat-bar-green { background-color: #28a745; }
        .stat-bar-yellow { background-color: #ffc107; color: #333; }
        .stat-bar-red { background-color: #dc3545; }
        .stat-bar-neutral { background-color: #e0e0e0; color: #666; text-align: center; }


        /* --- Tabla Principal de Datos --- */
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        .data-table th, .data-table td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        .data-table th { 
            background-color: #f4f4f4; 
            font-weight: bold;
            font-size: 14px;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-missed { color: #dc3545; font-weight: bold; }

        /* --- Utilidades --- */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    
    <header class="clearfix">
        @if ($logoBase64)
            <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo">
        @endif
        <div class="header-title">
            <h1>Reporte de Tomas</h1>
            <p>{{ $medication->name }} ({{ $user->name }})</p>
        </div>
    </header>

    <footer>
        Generado el {{ now()->format('d/m/Y H:i A') }} - MedicinasApp
        <div class="page-number"></div>
    </footer>

    <main>
        <h3>Resumen del Periodo</h3>
        <p>Mostrando resultados desde el <strong>{{ $startDate->format('d/m/Y') }}</strong> hasta el <strong>{{ $endDate->format('d/m/Y') }}</strong>.</p>
        
        <table class="stats-container">
            <tr>
                <td class="stat-card">
                    <h3 class="color-blue">{{ $stats['total'] }}</h3>
                    <p>Total Programadas</p>
                </td>
                <td class="stat-card">
                    <h3 class="color-green">{{ $stats['completed'] }}</h3>
                    <p>Completadas</p>
                </td>
                <td class="stat-card">
                    <h3 class="color-red">{{ $stats['missed'] }}</h3>
                    <p>Omitidas</p>
                </td>
            </tr>
        </table>

        <h3>Tasa de Cumplimiento</h3>
        
        @php
            $barColorClass = 'stat-bar-green';
            if ($complianceRate < 75) $barColorClass = 'stat-bar-yellow';
            if ($complianceRate < 50) $barColorClass = 'stat-bar-red';
        @endphp

        <div class="stat-bar-container">
            @if($stats['total'] == 0)
                <div class="stat-bar-inner stat-bar-neutral" style="width: 100%;">
                    N/A (Sin tomas)
                </div>
            @elseif($complianceRate == 0)
                <div class="stat-bar-inner stat-bar-red" style="width: 100%;">
                    0%
                </div>
            @else
                <div class="stat-bar-inner {{ $barColorClass }}" style="width: {{ $complianceRate }}%;">
                    {{ $complianceRate }}%
                </div>
            @endif
        </div>
        <h3>Detalle de Tomas</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha Programada</th>
                    <th>Hora Programada</th>
                    <th>Estado</th>
                    <th>Fecha de Toma (Real)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($takes as $take)
                    <tr>
                        <td>{{ $take->scheduled_at->format('d/m/Y') }}</td>
                        <td>{{ $take->scheduled_at->format('h:i A') }}</td>
                        <td>
                            @if ($take->completed_at)
                                <span class="status-completed">Completada</span>
                            @else
                                <span class="status-missed">Omitida</span>
                            @endif
                        </td>
                        <td>
                            {{ $take->completed_at ? $take->completed_at->format('d/m/Y h:i A') : 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No se encontraron tomas en este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

</body>
</html>