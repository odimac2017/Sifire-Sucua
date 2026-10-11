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
            border-collapse: collapse;
            line-height: 12px;
        }

        .tabla-valores {
            width: 100%;
            margin: 10px auto;
            border-collapse: collapse;
            border: 1px solid #000;
            line-height: 16px;
            text-align: center;
        }

        .tabla-valores td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 16px;
            height: 40px;
            width: 50%;
        }

        .tabla-tarifas {
            border-collapse: collapse;
            width: 100%;
            line-height: 9px;
        }

        .tabla-tarifas th, .tabla-tarifas td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
        }

        .tabla-tarifas th {
            background-color: #E5E7EB;
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

        <div class="text-center text-14 bold mt-10">
            {{ $permiso->procesoPermiso->tipoPermiso->nombre}} N° <span style="color: red;">{{ $permiso->numero_formateado }}</span>
        </div>

        <table class="tabla-sin-borde mt-10" style="width: 70%;">
            <tr>
                <td>
                    <span class="bold">Fecha:</span>
                    <span class="value">{{ strtoupper(\Carbon\Carbon::parse($permiso->fecha_expedicion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Proyecto:</span>
                    <span class="bold" style="color: red;">{{ $permiso->p_nombre_proyecto }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Área total:</span>
                    <span class="value">{{ $permiso->p_area_total }} m<sup>2</sup></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Establecimiento:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->e_nombre_comercial }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">CC o RUC:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->documento }}</span>
                </td>
            </tr>            
            <tr>
                <td>
                    <span class="label">Nombre:</span>
                    <span class="value">{{ $permiso->procesoPermiso->cliente->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Profesional:</span>
                    <span class="value">{{ $permiso->p_nombre_profesional }}</span>
                </td>           
            <tr>
                <td>
                    <span class="label">Ubicación:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->provincia->nombre }} / {{ $permiso->procesoPermiso->actividadEconomica->canton->nombre }} / {{ $permiso->procesoPermiso->actividadEconomica->parroquia->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Dirección:</span>
                    <span class="value">{{ $permiso->procesoPermiso->actividadEconomica->e_direccion }}</span>
                </td>
            </tr>            
        </table>
        
        <div class="block-justify mt-20">
            Por haber cumplido con los requisitos puntualizados en el Reglamento General de la Ley.
        </div>
        @if(!empty($permiso->observaciones))
            <div class="block-justify mt-5">
                <span class="bold">Observaciones:</span> {{ $permiso->observaciones }}
            </div>
        @endif

        <div style="position:absolute; top:90; left:350;">   
            <table class="tabla-tarifas text-11">
                <tr>
                    <th>Tarifario</th>
                    <th>Área</th>
                </tr>
                @foreach ($permiso->procesoPermiso->tarifarios as $tarifa)
                    <tr class="text-center">
                        <td width="70%">{{ $tarifa->tarifario->nombre }}</td>
                        <td width="30%">{{ $permiso->p_area_total }} m<sup>2</sup></td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div style="position:absolute; top:150; left:350; right: 0;">  
            <table class="tabla-valores">
                <tr>
                    <td>VALOR</td>
                    <td><span style="color: red;">$ {{ number_format($permiso->valor_total, 2) }}</span></td>
                </tr>
            </table> 
        </div>

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