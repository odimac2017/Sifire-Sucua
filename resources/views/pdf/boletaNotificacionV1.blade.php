<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Notificación #{{ $notificacion->numero_formateado }}</title>
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
            margin-top: 2em;
            margin-left: 2em;
            margin-right: 2em;
            margin-bottom: 2em;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 11px;
            position: relative;
        }

        .footer-boleta {
            position: absolute;
            bottom: 15px;
            left: 0;
            width: 100%;
        } 

        .marco-boleta {
            border: 3px double #000;
            padding: 15px;
            height: 458px;
        }

        .content {
            position: relative;
        }

        .institucion {
            margin-top: 0px;
            left: 20px;
            right: 20px;
            text-align: center;    
        }

        .logo {
            position: absolute;
            top: 0px;
            left: 0px;
            width: 80px;
        }

        .logo-agua {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            opacity: 0.20;
            z-index: -1;
        }     

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .text-10 { font-size: 10px; }
        .text-14 { font-size: 14px; }
        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }

        .label {
            font-weight: bold;
        }

        .value {
            margin-left: 5px;
        }

        .tabla-sin-borde {
            width: 100%;
            border-collapse: collapse;
            line-height: 14px;
        }   

        .check {
            display: inline-block;
            border: 1px solid #000;
            width: 18px;
            height: 16px;
            text-align: center;
            vertical-align: bottom;
            font-weight: bold;
        }

        .tabla-firmas {
            margin-bottom: -25px;
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
<div class="marco-boleta">
    <div class="content">
        <img class="logo" src="{{ asset('images/logo1.jpg') }}" alt="Logo">        
        <img class="logo-agua" src="{{ asset('images/logo1.jpg') }}" alt="">     
        <div class="institucion">
            <div class="bold text-14">{{ $institucion->razon_social }}</div>
            <div class="text-10">Teléfono: {{ $institucion->telefonos }} - email: {{ $institucion->email }}</div>
            <div class="text-10">Dirección: {{ $institucion->direccion }} - RUC: {{ $institucion->ruc }}</div>
        </div>

        <div class="text-center bold mt-5">
            JEFATURA DE PREVENCIÓN DE INCENDIOS
        </div>

        <div class="text-center text-14 bold mt-10">
            BOLETA DE NOTIFICACIÓN N° <span style="color: red;">{{ $notificacion->numero_formateado }}</span>
        </div>

        <table class="tabla-sin-borde mt-10">
            <tr>
                <td class="text-justify">
                    <i>La Jefatura de Prevención de Incendios del {{$institucion->razon_social}}, comunica a Usted que deberá obtener el <strong>PERMISO DE FUNCIONAMIENTO</strong>
                    de la actividad económica<strong>@if ($notificacion->procesoPermiso?->actividadEconomica?->tipo_actividad == 'E')
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->e_nombre_comercial }}
                    @elseif ($notificacion->procesoPermiso?->actividadEconomica?->tipo_actividad == 'V')
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->v_placa }}
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->v_marca }}
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->v_tipo }}
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->v_clase }}
                        {{ $notificacion->procesoPermiso?->actividadEconomica?->v_color }}
                    @endif</strong>por el año<strong>{{ \Carbon\Carbon::parse($notificacion->fecha_entrega)->year }}</strong>
                    en un plazo de<strong>@if ($notificacion->numero_notificacion === 1)
                        {{ $configuracion->plazo_primera_notificacion }}    
                    @elseif ($notificacion->numero_notificacion === 2)
                        {{ $configuracion->plazo_segunda_notificacion }}  
                    @else 
                        {{ $configuracion->plazo_tercera_notificacion }}  
                    @endif
                    DÍAS</strong>, caso contrario será clausurado de acuerdo al Art. No. 35 de la Ley de Defensa Contra Incendios.</i>
                </td>
            </tr>
        </table>
        <table class="tabla-sin-borde mt-10">
            <tr>
                <td>
                    <span class="bold">Recibe:</span>
                    <span class="bold">
                        @if ($notificacion->numero_notificacion === 1)
                            PRIMERA 
                        @elseif ($notificacion->numero_notificacion === 2)
                            SEGUNDA
                        @else 
                            TERCERA
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">CC o RUC:</span>
                    <span class="value">{{ $notificacion->procesoPermiso->cliente->documento }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Nombre o Razón Social:</span>
                    <span class="value">{{ $notificacion->procesoPermiso->cliente->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Representante legal:</span>
                    <span class="value">{{ $notificacion->nombre_representante_legal }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Dirección:</span>
                    <span class="value">{{ $notificacion->procesoPermiso->cliente->direccion }}</span>
                </td>
            </tr>
            <td class="text-justify">
                <span class="bold">Observaciones:</span>
                <span class="value">{{ $notificacion->observaciones }}</span>
            </td>            
        </table>
        <div class="footer-boleta">
            <table class="tabla-firmas">
                <tr>                    
                    <td>
                        <div class="linea-firma"></div>                    
                        <div>{{ $notificacion->nombre_personal }}</div>
                        <div class="cargo-firma">{{ ucwords(mb_strtolower($notificacion->personal->documento)) }}</div>
                        <div class="bold">INSPECTOR</div>
                    </td>
                    <td>
                        <div class="linea-firma"></div>                              
                        <div>{{ $notificacion->nombre_persona_recibe }}</div>
                        <div class="cargo-firma">{{ ucwords(mb_strtolower($notificacion->documento_persona_recibe)) }}</div> 
                        <div class="bold">RECIBE</div>                   
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>