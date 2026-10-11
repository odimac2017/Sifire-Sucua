<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Permiso de funcionamiento vehicular #{{ $permiso->numero_formateado }}</title>
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

        .footer-permiso {
            position: absolute;
            text-align: center;
            bottom: 10px;
            left: 0;
            width: 100%;
        } 

        .marco-permiso {
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

        .logo1 {
            position: absolute;
            top: 0px;
            left: 0px;
            width: 85px;
        }

        .logo2 {
            position: absolute;
            top: 0px;
            right: -8px;
            width: 85px;
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

        .text-left { text-align: left; }
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
            line-height: 12px;
        }

        .tabla-valores {
            width: 50%;
            margin: 10px auto;
            border-collapse: collapse;
            border: 1px solid #000;
            line-height: 16px;
        }

        .tabla-valores td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 16px;
            height: 40px;
            width: 50%;
        }

        .tabla-valores td:first-child {
            text-align: center;
        }

        .tabla-valores td:last-child {
            text-align: right;
        }

        .tabla-fechas {
            margin-top: -10px;
            width: 100%;
        }

        .tabla-fechas td {
            padding-top: 5px;
        }

        .observaciones {
            max-height: 40px;
            overflow: hidden;
        }

        .block-justify {
            text-align: justify;
            margin-left: 3px;
            margin-right: 3px;
        }

        .tabla-firmas {
            margin-bottom: -20px;
            left: 0;
            width: 100%;
        }

        .tabla-firmas td {
            text-align: center;
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

        .marcador-firma{
            color: white;
            font-size: 1px;
            line-height: 1px;
        }

        .footer-qr {
            position: absolute;
            bottom: -10px;
            left: 41%;
            text-align: center;
        }

        .qr img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        .qr-text {
            margin-top: -10px;
            color: red;
            font-size: 8px;
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="marco-permiso">
    <div class="content">
        <img class="logo1" src="{{ asset('images/logo1.jpg') }}" alt="Logo">
        <img class="logo2" src="{{ asset('images/logo2.jpg') }}" alt="Logo">
        <img class="logo-agua" src="{{ asset('images/logo1.jpg') }}" alt="">

        <div class="institucion">
            <div class="bold text-14" >{{ $institucion->razon_social }}</div>
            <div class="text-10">Teléfono: {{ $institucion->telefonos }} - email: {{ $institucion->email }}</div>
            <div class="text-10">Dirección: {{ $institucion->direccion }} - RUC: {{ $institucion->ruc }}</div>
        </div>

        <div class="text-center bold mt-5">
            JEFATURA DE PREVENCIÓN DE INCENDIOS
        </div>

        <div class="text-center text-10 italic">
            En uso de sus atribuciones establecidas en la Ley de Defensa Contra Incendios, en el Art. 35 concede el presente:
        </div>

        <div class="text-center text-14 bold mt-10">
            COMPROBANTE DE INSPECCIÓN VEHICULAR N° <span style="color: red;">{{ $permiso->numero_formateado }}</span>
        </div>
        <table class="tabla-sin-borde mt-10">
            <tr>
                <td width="33%">
                    <span class="label">N° extintores:</span>
                    <span class="value">{{ $permiso->procesoPermiso->extintores->count() }}</span>
                </td>
                <td colspan="2">
                    <span class="label">Código extintores:</span>
                    <span class="value">
                        @php
                            $numeros = $permiso->procesoPermiso->extintores->pluck('numero')->sort()->values();
                        @endphp

                        @if($numeros->count() > 5)
                            {{ str_pad($numeros->first(), 3, '0', STR_PAD_LEFT) }}
                            AL
                            {{ str_pad($numeros->last(), 3, '0', STR_PAD_LEFT) }}
                        @else
                            {{ $numeros->map(fn($c) => str_pad($c, 3, '0', STR_PAD_LEFT))->implode(', ') }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="label">Propietario:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="label">CC o RUC:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->documento }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="label">Dirección:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->direccion }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-center">
                    <span class="label text-14">VEHÍCULO</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="label">Marca:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_marca }}</span>
                </td>
                <td>
                    <span class="label">Placa:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_placa }}</span>
                </td>
                <td>
                    <span class="label">Color:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_color }}</span>
                </td>                
            </tr>
            <tr>
                <td>
                    <span class="label">Cilindraje:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_cilindraje }}</span>
                </td>
                <td>
                    <span class="label">Tipo:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_tipo }}</span>
                </td>
                
                <td>
                    <span class="label">Clase:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->v_clase }}</span>
                </td>
            </tr>            
        </table>
        <table class="tabla-valores">
            <tr>
                <td>VALOR</td>
                <td><span style="color: red;">$ {{ number_format($permiso->valor_total, 2) }}</span></td>
            </tr>
        </table>
        <table class="tabla-fechas">
            <tr>
                <td>
                    <span class="bold">Fecha de expedición:</span>
                    {{ strtoupper(\Carbon\Carbon::parse($permiso->fecha_expedicion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}
                </td>

                <td class="text-right">
                    <span class="bold">Fecha de caducidad:</span>
                    {{ strtoupper(\Carbon\Carbon::parse($permiso->fecha_caducidad)->translatedFormat('d \\d\\e F \\d\\e Y')) }}
                </td>
            </tr>
        </table>
        <div class="block-justify">
            Por haber cumplido con los requisitos puntualizados en el Reglamento General de la Ley.
        </div>
        @if(!empty($permiso->observaciones))
            <div class="block-justify mt-5 text-10">
                <span class="bold">Observaciones:</span> {{ $permiso->observaciones }}
            </div>
        @endif

        <div class="footer-permiso">           
            @php
                $firmantes = $permiso->plantilla->personalPlantilla ?? collect();

                if ($firmantes->isEmpty()) {
                    $firmas = collect([
                        ['nombre'=>null,'puesto'=>'JEFE DEL CUERPO DE BOMBEROS','marcador'=>null],
                        ['nombre'=>null,'puesto'=>'RECAUDADOR/A','marcador'=>null],
                    ]);
                } else {
                    $firmas = $firmantes->map(function ($f) {
                        return [
                            'nombre' => $f->firmante->nombre ?? null,
                            'puesto' => $f->puesto,
                            'marcador' => $f->marcador_firma
                        ];
                    })->filter(fn($f)=>$f['puesto'])->values();
                }

                $count = $firmas->count();

                if ($count == 1) {
                    $anchos = ['100%'];
                } elseif ($count == 2) {
                    $anchos = ['40%','20%','40%'];
                } else {
                    $anchos = ['33.33%','33.33%','33.33%'];
                }
            @endphp

            <table class="tabla-firmas">
                <tr>
                    @if($count == 1)
                        <td width="100%">
                            {{-- Marcador para el microservicio --}}
                            <div class="marcador-firma">
                                {{ $firmas[0]['marcador'] }}
                            </div>
                            <div class="linea-firma"></div>
                            <div class="nombre-firma">
                                {{ ucwords(mb_strtolower($firmas[0]['nombre'])) }}
                            </div>        
                            <div class="cargo-firma">{{ $firmas[0]['puesto'] }}</div>
                        </td>
                    @elseif($count == 2)
                        <td width="40%">
                            {{-- Marcador para el microservicio --}}
                            <div class="marcador-firma">
                                {{ $firmas[0]['marcador'] }}
                            </div>
                            <div class="linea-firma"></div>
                            <div class="nombre-firma">
                                {{ ucwords(mb_strtolower($firmas[0]['nombre'])) }}
                            </div>        
                            <div class="cargo-firma">{{ $firmas[0]['puesto'] }}</div>
                        </td>
                        <td width="20%"></td>
                        <td width="40%">
                            {{-- Marcador para el microservicio --}}
                            <div class="marcador-firma">
                                {{ $firmas[1]['marcador'] }}
                            </div>
                            <div class="linea-firma"></div>
                            <div class="nombre-firma">
                                {{ ucwords(mb_strtolower($firmas[1]['nombre'])) }}
                            </div>        
                            <div class="cargo-firma">{{ $firmas[1]['puesto'] }}</div>
                        </td>
                    @else
                        @foreach($firmas as $firma)
                            <td width="33.33%">
                                {{-- Marcador para el microservicio --}}
                                <div class="marcador-firma">
                                    {{ $firma['marcador'] }}
                                </div>
                                <div class="linea-firma"></div>
                                <div class="nombre-firma">
                                    {{ ucwords(mb_strtolower($firma['nombre'])) }}
                                </div>            
                                <div class="cargo-firma">{{ $firma['puesto'] }}</div>
                            </td>
                        @endforeach
                    @endif
                </tr>
            </table>        
        </div>
        <div class="footer-qr">
            <div class="qr">
                <img src="data:image/png;base64, {{ $qrBase64 }}">
                <div class="qr-text">
                    Verifique la validez del documento
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>