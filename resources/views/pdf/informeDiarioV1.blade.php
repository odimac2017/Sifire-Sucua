<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe diario #{{ $informeDiario->numero }}</title>
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
            margin-top: 12em;
            margin-left: 5em;
            margin-right: 5em;
            margin-bottom: 5em;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 12px;
            position: relative;
        }

        #membrete {
            position: fixed;
            top: -12em;
            left: -10em;
            right: -10em;
            height: 140%;
            background-image: url("{{ asset('storage/plantillas/plantilla4.jpg') }}");
            background-position: center top;
            z-index: -1;
        }

        header { 
            position: fixed;
            top: -4em;
            left: 0;
            right: 0;
            height: 6em;
            text-align: center;
            line-height: 8px;
        }                

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify {text-align: justify; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .text-10 { font-size: 10px; }
        .text-14 { font-size: 14px; }
        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }

        .tabla-sin-borde {
            width: 100%;
            border-collapse: collapse;
            line-height: 14px;
            page-break-inside: avoid;
        }

        .tabla {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            line-height: 6px;
            page-break-inside: avoid;
        }

        .tabla th {
            background:#d9d9d9;
            text-align:center;
            padding: 6px;
            font-weight:bold;
            border: 1px solid #000; 
        }

        .tabla td {
            padding: 10px 8px;
            vertical-align: middle;
            border: 1px solid #000;            
        }

        .bloque {
            margin-top: 5px;
            margin-left: -6px;
            margin-right: -6px;
        }

        .contenido-bloque {
            padding: 0 8px 0 8px;
            text-align: justify;
            line-height: 14px;
        }

        .slogan {
            margin-top: 30px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .tabla-firmas {
            margin-top: 70px;
            left: 0;
            width: 100%;
            text-align: center;
            page-break-inside: avoid;
        }

        .tabla-firmas td {
            width: 33%;
            vertical-align: top;
        }

        .linea-firma {
            border-top: 1px solid #000;
            width: 185px;
            margin: 0 auto 5px auto;
        }

        .cargo-firma {
            font-weight: bold;
            font-size: 11px;
        }

        .marcador-firma{
            color: white;
            font-size: 1px;
            line-height: 1px;
        }
    </style>
</head>
<body>
    <div id="membrete"></div>
    <header>
        <div class="text-right bold mt-10" style="line-height: 12px;">
            {{ $informeDiario->numero}}
        </div>

        <div class="text-right mt-5" style="line-height: 12px;">
            Sucúa, {{ \Carbon\Carbon::parse($informeDiario->fecha_informe)->translatedFormat('d \\d\\e F \\d\\e Y') }}
        </div>
    </header>
    <div class="content">
        <div class="text-left" style="line-height: 4px;"> 
            <p>{{ ucwords(mb_strtolower($informeDiario->personalPuestoDirige->personal->titulo->nombre)) }}</p>
            <p>{{ ucwords(mb_strtolower($informeDiario->personalPuestoDirige->personal->nombres)) }} {{ ucwords(mb_strtolower($informeDiario->personalPuestoDirige->personal->apellidos)) }}</p>
            <p class="bold">{{ $informeDiario->puesto_dirige}}</p>
            <p>Ciudad.-</p>
        </div>

        <div class="text-left mt-14">
            De mi consideración:
        </div>

        <div class="bloque">
            <div class="contenido-bloque">
                {!! $informeDiario->contenido !!}
            </div>
        </div>
        
        <table class="tabla mt-10" style="width:60%; margin: 0 auto;">
            <tr>
                <th width="70%"></td>
                <th width="30%"><strong>CANTIDAD</strong></th>
            </tr>        
            <tr>
                <td>INSPECCIONES APROBADAS</td>
                <td class="text-center"><strong>{{ $informeDiario->inspecciones_aprobadas}}</strong></td>
            </tr>       
            <tr>
                <td>LOCALES SUJETOS A REINSPECCIÓN</td>
                <td class="text-center"><strong>{{ $informeDiario->inspecciones_reinspeccion}}</strong></td>
            </tr>
            <tr>
                <td>INSPECCIONES SIN REALIZAR</td>
                <td class="text-center"><strong>{{ $informeDiario->inspecciones_no_realizadas}}</strong></td>
            </tr>
            <tr>
                <td>INSPECCIONES VEHICULARES</td>
                <td class="text-center"><strong>{{ $informeDiario->inspecciones_vehiculares}}</strong></td>
            </tr>
            <tr>
                <td>NOTIFICACIONES REALIZADAS</td>
                <td class="text-center"><strong>{{ $informeDiario->notificaciones_realizadas}}</strong></td>
            </tr>
            <tr>
                <th><strong>TOTAL GENERAL</strong></td>
                <th><strong>{{ $informeDiario->total_general}}</strong></th>
            </tr> 
        </table>

        <div class="bloque">
            <div class="contenido-bloque">
                {!! $informeDiario->observaciones !!}
            </div>
        </div>

        <div class="bloque">
            <div class="contenido-bloque">
                {!! $informeDiario->despedida !!}
            </div>
        </div>

        <div style="page-break-inside: avoid;">
            <table class="tabla-sin-borde mt-20">    
                <tr>
                    <td class="text-center">Atentamente,</td>
                </tr>
                <tr>
                    <td class="text-center slogan">{{ $institucion->slogan}}</td>
                </tr>
            </table>

            @php
                $firmas = [
                    [
                        'nombre' => $informeDiario->nombre_personal,
                        'puesto' => $informeDiario->puesto_personal,
                        'marcador' => '__FIRMA_RESPONSABLE__'
                    ],
                ];           
            @endphp
            <table class="tabla-firmas">
                <tr>
                    @foreach ($firmas as $firma)
                        <td>
                            {{-- Marcador para el microservicio --}}
                            <div class="marcador-firma">
                                {{ $firma['marcador'] }}
                            </div>
                            
                            @if ($firma['puesto'])   
                                <div class="linea-firma"></div>
                            @endif
                            
                            @if ($firma['nombre'])                            
                                <div>{{ ucwords(mb_strtolower($firma['nombre'])) }}</div>
                            @endif

                            <div class="cargo-firma">{{ $firma['puesto'] }}</div>
                        </td>
                    @endforeach
                </tr>
            </table>  
        </div>        
    </div>
</div>
</body>
</html>