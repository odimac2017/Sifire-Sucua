<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parte diario de consignas</title>
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
            margin-top: 3em;
            margin-left: 3em;
            margin-right: 3em;
            margin-bottom: 6em;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 12px;
            position: relative;
        }

        #membrete {
            position: fixed;
            top: -3em;
            right: -10em;
            bottom: -60em;
            left: -10em;            
            background-image: url("{{ asset('storage/plantillas/plantilla3.jpg') }}");
            background-position: center top;
            z-index: -1;
        }

        header { 
            position: fixed;
            top: 5em;
            left: 0;
            right: 0;
            height: 6em;
            text-align: center;
            line-height: 8px;
        }        

        .content {
            position: relative;
            top: 8em;
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
            padding: 6px 4px;
            vertical-align: middle;
            border: 1px solid #000;            
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
            margin-top: 90px;
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
            width: 180px;
            margin: 0 auto 5px auto;
        }

        .cargo-firma {
            font-weight: bold;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div id="membrete"></div>
    <header>        
        <div class="text-center text-14 bold mt-5">
            TURNO DE GUARDIA N° <span style="color: red;">{{ $parteConsigna->turnoGuardia->nombre }}</span>
        </div> 

        <div class="text-center text-14 bold mt-10">
            FECHA: {{ strtoupper(\Carbon\Carbon::parse($parteConsigna->fecha)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</span>
        </div> 
    </header>
    <div class="content">
        @if ($parteConsigna->integrantes->whereNotNull('personal_id')->isNotEmpty())
            <table class="tabla mt-5">
                <tr>
                    <th colspan="2"><strong>Nómina operativa</strong></th>
                </tr>
                <tr>
                    <td width="30%"><strong>OFICIAL DE GUARDIA:</strong></td>
                    <td width="70%">{{ $parteConsigna->nombre_entrega }}</td>
                </tr>

                @foreach ($parteConsigna->integrantes->whereNotNull('personal_id') as $integrante)
                    <tr>
                        <td><strong>{{ $integrante->nombre_funcion }}:</strong></td>
                        <td>{{ $integrante->nombre_personal }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if ($parteConsigna->vehiculos->isNotEmpty())
            <table class="tabla mt-10">    
                <tr>
                    <th colspan="6"><strong>Vehículos operativos</strong></th>
                </tr>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="25%" class="text-center"></th>
                    <th width="15%" class="text-center"></th>
                    <th width="5%" class="text-center">SI</th>
                    <th width="5%" class="text-center">NO</th>
                    <th width="45%" class="text-center">Motivo</th>
                </tr>
                @foreach ($parteConsigna->vehiculos as $vehiculo)
                    <tr>
                        <td class="text-center">{{ $loop->index + 1 }}</td>
                        <td class="text-center">{{ $vehiculo->bien->tipo_vehiculo }} {{ $vehiculo->bien->marca }}</td>
                        <td class="text-center">{{ $vehiculo->bien->identificacion }}</td>
                        <td class="text-center"><span class="check {{ $vehiculo->operativo == '1' ? 'X' : '' }}">{{ $vehiculo->operativo == '1' ? 'X' : '' }}</span></td>
                        <td class="text-center"><span class="check {{ $vehiculo->operativo == '0' ? 'X' : '' }}">{{ $vehiculo->operativo == '0' ? 'X' : '' }}</span></td>
                        <td class="text-center">{{ $vehiculo->motivo }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if ($parteConsigna->reportesQap->isNotEmpty())
            <table class="tabla mt-10">
                <tr>
                    <th colspan="3"><strong>Observaciones del personal el QAP</strong></th>
                </tr>
                @foreach ($parteConsigna->reportesQap as $reporte)
                    <tr>
                        <td width="30%">{{ $reporte->pivot->nombre_personal ?? $reporte->nombre_personal }}</td>
                        <td width="10%" class="text-center"><strong>{{ \Carbon\Carbon::parse($reporte->pivot->hora)->format('H:i') }}</strong></td>
                        <td width="60%">{{ $reporte->pivot->reporte ?? $reporte->reporte }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if ($parteConsigna->consignas->isNotEmpty())
            <table class="tabla mt-10">                
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="25%" class="text-center">Responsable</th>
                    <th width="30%" class="text-center">Consignas / actividades</th>
                    <th width="5%" class="text-center">SI</th>
                    <th width="5%" class="text-center">NO</th>
                    <th width="30%" class="text-center">Motivo</th>
                </tr>
                @foreach ($parteConsigna->consignas as $consigna)
                    <tr>
                        <td class="text-center">{{ $loop->index + 1 }}</td>
                        <td class="text-center">{{ $consigna->nombre_personal }}</td>
                        <td class="text-center">{{ $consigna->consigna }}</td>
                        <td class="text-center"><span class="check {{ $consigna->cumple == '1' ? 'X' : '' }}">{{ $consigna->cumple == '1' ? 'X' : '' }}</span></td>
                        <td class="text-center"><span class="check {{ $consigna->cumple == '0' ? 'X' : '' }}">{{ $consigna->cumple == '0' ? 'X' : '' }}</span></td>
                        <td class="text-center">{{ $consigna->motivo }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if ($parteConsigna->planificaciones->isNotEmpty())
            <table class="tabla mt-10">    
                <tr>
                    <th colspan="6"><strong>Planificación diaria del grupo de guardia</strong></th>
                </tr>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="25%" class="text-center">Responsable</th>
                    <th width="30%" class="text-center">Actividad</th>
                    <th width="5%" class="text-center">SI</th>
                    <th width="5%" class="text-center">NO</th>
                    <th width="30%" class="text-center">Motivo</th>
                </tr>
                @foreach ($parteConsigna->planificaciones as $planificacion)
                    <tr>
                        <td class="text-center">{{ $loop->index + 1 }}</td>
                        <td class="text-center">{{ $planificacion->pivot->nombre_personal ?? $planificacion->nombre_personal }}</td>
                        <td class="text-center">{{ $planificacion->pivot->actividad ?? $planificacion->actividad }}</td>
                        <td class="text-center"><span class="check {{ $planificacion->pivot->cumple == '1' ? 'X' : '' }}">{{ $planificacion->pivot->cumple == '1' ? 'X' : '' }}</span></td>
                        <td class="text-center"><span class="check {{ $planificacion->pivot->cumple == '0' ? 'X' : '' }}">{{ $planificacion->pivot->cumple == '0' ? 'X' : '' }}</span></td>
                        <td class="text-center">{{ $planificacion->pivot->motivo ?? $planificacion->motivo }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if (!empty($parteConsigna->novedades))
            <table class="tabla mt-10">    
                <tr>
                    <th><strong>Novedades durante la guardia</strong></th>
                </tr>
                <tr>
                    <td class="text-justify">
                        {{ $parteConsigna->novedades }}
                    </td>
                </tr>
            </table>
        @endif
        
        <div style="page-break-inside: avoid;">            
            @php
                $firmas = [
                    ['nombre' => $parteConsigna->nombre_entrega, 'puesto' => 'OFICIAL QUE ENTREGA'],
                    ['nombre' => $parteConsigna->nombre_revisa, 'puesto' => 'OFICIAL QUE REVISA'],
                    ['nombre' => $parteConsigna->nombre_recibe, 'puesto' => 'OFICIAL QUE RECIBE'],
                ];           
            @endphp
            <table class="tabla-firmas">
                <tr>
                    @foreach ($firmas as $firma)
                        <td>
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