<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Permiso de funcionamiento #{{ $permiso->numero_formateado }}</title>
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
            bottom: 15px;
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

        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .text-10 { font-size: 10px; }
        .text-14 { font-size: 14px; }
        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .top { vertical-align: top; }

        .label {
            font-weight: bold;
        }

        .value {
            margin-left: 5px;
        }

        .tabla-sin-borde {
            width: 100%;
            border-collapse: collapse;
            line-height: 11px;
        }

        .tabla-valores {
            width: 60%;
            margin: 4px auto;
            border-collapse: collapse;
            border: 1px solid #000;
            line-height: 8px;
        }

        .tabla-valores th,
        .tabla-valores td {
            border: 1px solid #000;
            padding: 5px 8px;
        }

        .tabla-valores th {
            text-align: center;
            font-weight: bold;
        }

        .tabla-valores td:first-child {
            text-align: left;
        }

        .tabla-valores td:last-child {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
        }

        .tabla-archivo {
            width: 100%;
            margin: 10px auto;
            border-collapse: collapse;
            border: 1px solid #000;
            line-height: 16px;
            text-align: center;
        }

        .tabla-archivo td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 16px;
            height: 40px;
        }

        .tabla-fechas {
            margin-top: -10px;
            width: 100%;
        }

        .tabla-fechas td {
            padding-top: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .block-justify {
            text-align: justify;
            margin-left: 3px;
            margin-right: 3px;
        }

        .tabla-firmas {
            margin-bottom: -25px;
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
            En uso de sus atribuciones establecidas en la Ley de Defensa Contra Incendios, en el Art. 35, concede el presente:
        </div>

        <table class="tabla-sin-borde mt-20">
            <tr>
                <td width="33%">
                    <span class="label">CC o RUC:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->documento }}</span>
                </td>
                <td width="33%">
                    <span class="label">Clave catastral:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->e_clave_catastral }}</span>
                </td>
                <td rowspan="3" class="top text-14 text-center">
                    <strong>{{ $permiso->procesoPermiso->tipoPermiso->nombre}}<br><span style="color: red;">{{ $permiso->numero_formateado }}</span></strong>
                </td>
            </tr>            
            <tr>
                <td colspan="2">
                    <span class="label">Razón Social:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Nombre comercial:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->e_nombre_comercial }}</span>
                </td>           
            <tr>
                <td>
                    <span class="label">Parroquia:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->parroquia->nombre }}</span>
                </td>
                <td colspan="2">
                    <span class="label">Dirección:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->e_direccion }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="label"># extintores:</span>
                    <span class="value">{{ $permiso->procesoPermiso->extintores->count() }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-justify">
                    <span class="label">Tipo de establecimiento:</span>
                    <span class="value">
                        @foreach ($permiso->procesoPermiso->tarifarios as $tarifa)
                            {{ $tarifa->tarifario->nombre }}@if(!$loop->last), @endif
                        @endforeach
                    </span>
                </td>
            </tr>
        </table>
        <table class="tabla-valores">
            <tr style="background-color: #CBCBCB;">
                <th width="70%">DESCRIPCIÓN</th>
                <th width="30%">VALOR</th>
            </tr>
            <tr>
                <td>Valor de inspección</td>
                <td>{{ number_format($permiso->valor_inspeccion ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Valor del permiso <strong>{{ \Carbon\Carbon::parse($permiso->fecha_expedicion)->year }}</strong></td>
                <td>{{ number_format($permiso->valor_permiso ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Valor de reinspección</td>
                <td>{{ number_format($permiso->valor_reinspeccion ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row" style="background-color: #CBCBCB;">
                <td style="text-align:center;">TOTAL</td>
                <td><span class="text-14">$ {{ number_format($permiso->valor_total, 2) }}</span></td>
            </tr>
        </table>

        <div style="position:absolute; top:150; left:440; right: 0;">  
            <table class="tabla-archivo">
                <tr>
                    <td>{{ $permiso->procesoPermiso->cliente->archivo_fisico}}</td>
                </tr>
            </table> 
        </div>

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