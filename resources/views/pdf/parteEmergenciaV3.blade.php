<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parte de emergencia #{{ $parteEmergencia->numero }}</title>
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
            margin-top: 16em;
            margin-left: 3em;
            margin-right: 3em;
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
            top: -16em;
            left: -10em;
            right: -10em;
            height: 140%;
            background-image: url("{{ asset('storage/plantillas/plantilla4.jpg') }}");
            background-position: center top;
            z-index: -1;
        }

        header { 
            position: fixed;
            top: -7em;
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
            line-height: 10px;
            page-break-inside: avoid;
        }

        .tabla th {
            background:#d9d9d9;
            text-align:center;
            padding:2px;
            font-weight:bold;
            border: 1px solid #000; 
        }

        .tabla td {
            padding: 10px 8px;
            vertical-align: middle;
            border: 1px solid #000;            
        }

        .bloque {
            border: 1px solid #000;
            margin-top: 5px;
        }

        .titulo-bloque {
            display: block;
            background: #d9d9d9;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            width: 99.98%;
        }

        .contenido-bloque {
            padding: 0 8px 0 8px;
            text-align: justify;
            line-height: 14px;
        }

        .check {
            display: inline-block;
            border: 1px solid #000;
            width: 13px;
            height: 13px;
            text-align: center;
            line-height: 11px;
            font-weight: bold;
            vertical-align: bottom;
            margin-bottom: 3px;
        }

        .check.X {
            color: red;
            border-color: red;
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
        <div class="bold text-14">COORDINACIÓN GENERAL OPERATIVA</div>           
        
        <div class="text-center bold mt-10" style="line-height: 12px;">
            Nombre del desastre, operación o emergencia: 
            @foreach ($parteEmergencia->categorias as $categoria)
                {{ $categoria->categoria2 }}
                @if (!$loop->last)
                    -
                @endif
            @endforeach
        </div>

        <div class="text-center text-14 bold mt-20">
            PARTE DE INCIDENTE N° <span style="color: red;">{{ $parteEmergencia->numero }}</span>
        </div> 
    </header>
    <div class="content">
        <table class="tabla-sin-borde">
            <tr>
                <td width="33%" class="text-left">Inicial <span class="check {{ $parteEmergencia->inicial == '1' ? 'X' : '' }}">{{ $parteEmergencia->inicial == '1' ? 'X' : '' }}</span></td>
                <td width="33%" class="text-center">Seguimiento <span class="check {{ $parteEmergencia->seguimiento == '1' ? 'X' : '' }}">{{ $parteEmergencia->seguimiento == '1' ? 'X' : '' }}</span></td>
                <td width="33%" class="text-right">Final <span class="check {{ $parteEmergencia->final == '1' ? 'X' : '' }}">{{ $parteEmergencia->final == '1' ? 'X' : '' }}</span></td>
            </tr>
        </table>
        <table class="tabla mt-10">
            <tr>
                <td width="50%"><strong>Fecha de llamada:</strong> {{ strtoupper(\Carbon\Carbon::parse($parteEmergencia->fecha_llamada)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                <td width="50%"><strong>Hora:</strong> {{ \Carbon\Carbon::parse($parteEmergencia->fecha_llamada)->format('H:i') }}</td>
            </tr>        
            <tr>
                <td><strong>Fecha de llegada:</strong> {{ strtoupper(\Carbon\Carbon::parse($parteEmergencia->fecha_llegada)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                <td><strong>Hora:</strong> {{ \Carbon\Carbon::parse($parteEmergencia->fecha_llegada)->format('H:i') }}</td>
            </tr>       
            <tr>
                <td><strong>Fecha de atención:</strong> {{ strtoupper(\Carbon\Carbon::parse($parteEmergencia->fecha_atencion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                <td><strong>Hora:</strong> {{ \Carbon\Carbon::parse($parteEmergencia->fecha_atencion)->format('H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Fecha de elaboración del informe:</strong> {{ strtoupper(\Carbon\Carbon::parse($parteEmergencia->created_at)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                <td><strong>Hora:</strong> {{ \Carbon\Carbon::parse($parteEmergencia->created_at)->format('H:i') }}</td>
            </tr>
        </table>
        <table class="tabla mt-5">
            <tr>
                <td width="50%"><strong>Provincia:</strong> {{ $parteEmergencia->provincia->nombre }}</td>
                <td width="50%"><strong>Fuente:</strong> {{ $parteEmergencia->fuente }}</td>
            </tr>
            <tr>
                <td><strong>Cantón:</strong> {{ $parteEmergencia->canton->nombre }}</td>
                <td><strong>Parroquia:</strong> {{ $parteEmergencia->parroquia->nombre }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Sector / dirección:</strong> {{ $parteEmergencia->direccion }}</td>
            </tr>
        </table>
        <div class="bloque">
            <div class="titulo-bloque">
                Contexto general
            </div>

            <div class="contenido-bloque">
                {!! $parteEmergencia->contexto_general !!}
            </div>
        </div>
        @if ($parteEmergencia->personal_radio_id)
            <table class="tabla mt-5">    
                <tr>
                    <th><strong>Radio operador</strong></th>
                </tr>
                <tr>
                    <td class="text-justify">{{ $parteEmergencia->nombre_radio }}</td>
                </tr>
            </table>
        @endif
        @if ($parteEmergencia->personales->isNotEmpty())
            <table class="tabla mt-5">    
                <tr>
                    <th><strong>Personal de respuesta</strong></th>
                </tr>
                <tr>
                    <td class="text-justify" style="padding-top: 0px; padding-bottom: 0px;">
                        <ul>
                            @foreach ($parteEmergencia->personales as $personal)
                                <li>{{ $personal->pivot->nombre_personal ?? $personal->nombre_personal }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            </table>
        @endif
        @if ($parteEmergencia->personalesQap->isNotEmpty())
            <table class="tabla mt-5">    
                <tr>
                    <th colspan="5"><strong>Personal de QAP (Apoyo en emergencia)</strong></th>
                </tr>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="40%" class="text-center">Nombre</th>
                    <th width="20%" class="text-center">Desde</th>
                    <th width="20%" class="text-center">Hasta</th>
                    <th width="15%" class="text-center">Tiempo total</th>
                </tr>
                @foreach ($parteEmergencia->personalesQap as $personal)
                    <tr>
                        <td class="text-center">{{ $loop->index + 1 }}</td>
                        <td class="text-center">{{ $personal->pivot->nombre_personal ?? $personal->nombre_personal}}</td>
                        <td class="text-center">{{ $personal->pivot->desde ?? $personal->desde }}</td>
                        <td class="text-center">{{ $personal->pivot->hasta ?? $personal->hasta }}</td>
                        <td class="text-center">{{ $personal->pivot->tiempo_total ?? $personal->tiempo_total }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
        @if (!empty($parteEmergencia->personal_externo))
            <div class="bloque">
                <div class="titulo-bloque">
                    Personal de apoyo externo
                </div>

                <div class="contenido-bloque">
                    {!! $parteEmergencia->personal_externo !!}
                </div>
            </div>
        @endif
        @if ($parteEmergencia->insumos->isNotEmpty())
            <table class="tabla mt-5">    
                <tr>
                    <th colspan="4"><strong>Insumos utilizados</strong></th>
                </tr>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="55%" class="text-center">Nombre</th>
                    <th width="20%" class="text-center">Presentación</th>
                    <th width="20%" class="text-center">Cantidad</th>
                </tr>
                @foreach ($parteEmergencia->insumos as $insumo)
                    <tr>
                        <td class="text-center">{{ $loop->index + 1 }}</td>
                        <td class="text-center">{{ $insumo->nombre }}</td>
                        <td class="text-center">{{ $insumo->presentacion }}</td>
                        <td class="text-center">{{ $insumo->pivot->cantidad ?? $insumo->cantidad }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
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
                        'nombre' => $parteEmergencia->nombre_personal,
                        'puesto' => 'OBAC',
                        'marcador' => '__FIRMA_OBAC__'
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
        @if ($parteEmergencia->fotos->isNotEmpty()) 
            <div style="page-break-after:always;"></div>

            <table class="tabla">
                <tr>
                    <th colspan="2"><strong>Registro fotográfico</strong></th>
                </tr>
                <tr>
                    @foreach ($parteEmergencia->fotos as $index => $foto)
                        <td style="text-align:center; padding:10px; width:50%;">
                            <img 
                                src="{{ public_path('storage/' . $foto->ruta) }}"
                                style="width:100%; max-height:250px; object-fit:cover; border:1px solid #ccc;"
                            >
                            <div style="font-size:10px; margin-top:5px;">
                                Foto {{ $index + 1 }}
                            </div>
                        </td>
                        @if (($index + 1) % 2 == 0)
                            </tr><tr>
                        @endif
                    @endforeach

                    {{-- si queda una celda sola, cerramos estructura --}}
                    @if ($parteEmergencia->fotos->count() % 2 != 0)
                        <td></td>
                    @endif
                </tr>
            </table>
        @endif
        @if ($parteEmergencia->ver_mapa)
            <div style="page-break-after:always;"></div>

            <table class="tabla">
                <tr>
                    <th><strong>Geolocalización</strong></th>
                </tr>
                <tr>
                    <td class="text-center">
                        @if(!empty($parteEmergencia->ruta_mapa) && file_exists(public_path($parteEmergencia->ruta_mapa)))
                            <img src="{{ asset($parteEmergencia->ruta_mapa) }}" width="680">                            
                        @else
                            <p><b>Ubicación no disponible</b></p>
                        @endif
                    </td>
                </tr>
            </table>
        @endif
    </div>
</div>
</body>
</html>