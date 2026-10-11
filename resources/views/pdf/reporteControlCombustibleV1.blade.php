<!DOCTYPE>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte de Control de Combustible</title>
    <style>
        @font-face {
            font-family: 'Roboto';
            src: url('fonts/Roboto-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('fonts/Roboto-Italic.ttf') format('truetype');
            font-weight: normal;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('fonts/Roboto-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @page {
            margin-top: 8em;
            margin-left: 3em;
            margin-right: 3em;
            margin-bottom: 3em;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 12px;
            position: relative;
        }

        header { 
            position: fixed;
            top: -7em;
            left: 0;
            right: 0;
            height: 6em;
            text-align: center;
        }

        header .logo1 {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 75px;
        }

        header .logo2 {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 75px;
        }

        header .institucion {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        header .institucion p {
            margin: 0;
        }

        header .reporte-info {
            position: absolute;
            top: 60px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-weight: bold;
        }

        header .reporte-info p {
            margin: 2px 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }

        .tabla-reporte {
            border-collapse: collapse;
            width: 100%;
        }

        .tabla-reporte th, .tabla-reporte td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
        }

        .tabla-reporte th {
            background-color: #E5E7EB;
        }

        .tabla-firmas {
            margin-top: 110px;
            left: 0;
            width: 100%;
            text-align: center;
        }

        .tabla-firmas td {
            width: 33%;
            vertical-align: top;
        }

        .linea-firma {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto 5px auto;
        }

        .cargo-firma {
            font-weight: bold;
            font-size: 11px;
        }
    </style>
    <body>
        <header>
            <img class="logo1" src="{{ asset('images/logo1.jpg') }}" alt="Logo">
            <img class="logo2" src="{{ asset('images/logo2.jpg') }}" alt="Logo">
            <div class="institucion">
                <p>{{$institucion->razon_social}}</p>
                <p>RUC: {{$institucion->ruc}}</p>
            </div>
            <div class="reporte-info">
                <p>REPORTE DE CONTROL DE COMBUSTIBLE</span></p>
                <p>{{ mb_strtoupper($periodo) }}</p>
            </div>
        </header>

        <main>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th width="2%">#</th>
                        <th width="10%">Fecha</th>
                        <th width="12%">Combustible</th>
                        <th width="20%">Chofer</th>
                        <th width="20%">Vehículo / equipo</th>
                        <th width="10%">Número de orden</th>
                        <th width="8%">Km de recorrido</th>
                        <th width="10%">Galones</th>
                        <th width="10%">Precio Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $cantidad_galones = 0; 
                        $valor_total = 0;                        
                    @endphp
                    @foreach ($reportes as $reporte)
                        <tr>
                            <td class="text-center">{{$loop->iteration}}</td>
                            <td class="text-center">{{$reporte->fecha}}</td>
                            <td class="text-center">{{$reporte->combustible->nombre}}</td>
                            <td class="text-center">{{$reporte->nombre_chofer}}</td>
                            <td class="text-center"><strong>{{$reporte->bien->identificacion}}</strong></td>
                            <td class="text-center">{{$reporte->numero_formateado}}</td>
                            <td class="text-center">{{ $reporte->km_recorrido > 0 ? $reporte->km_recorrido : '' }}</td>
                            <td class="text-center">{{ number_format($reporte->cantidad_galones, 2) }}</td>
                            <td class="text-right">{{ number_format($reporte->valor_total, 2) }}</td>
                        </tr>
                        @php
                            $cantidad_galones += $reporte->cantidad_galones; 
                            $valor_total += $reporte->valor_total; 
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="7" class="text-right font-bold">Totales:</td>
                        <td class="text-center font-bold">{{ number_format($cantidad_galones, 2) }}</td>
                        <td class="text-right font-bold">$ {{ number_format($valor_total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="tabla-firmas">
                <tr>
                    @foreach ($personal_firma as $firma)
                        <td>
                            @if ($firma->nombrePuesto)   
                                <div class="linea-firma"></div>
                            @endif
                            
                            @if ($firma->nombrePersonal)                            
                                <div>{{ ucwords(mb_strtolower($firma->nombrePersonal)) }}</div>
                            @endif

                            <div class="cargo-firma">{{ $firma->nombrePuesto }}</div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </main>
    </body>
</html>