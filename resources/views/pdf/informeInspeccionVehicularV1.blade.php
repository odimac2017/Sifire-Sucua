<!DOCTYPE>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informe de Inspección Vehicular #{{ $inspeccion->numero_formateado }}</title>
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
            margin-bottom: 0em; 
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 98%;
            text-align: center;
            margin-bottom: 10px;
            line-height: 11px;
            border: 1px solid #000;
            padding: 5px 5px 5px 5px;
        }

        .institucion {
            margin-top: 0px;
            left: 20px;
            right: 20px;
            text-align: center;    
        }       

        .logo1 {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 85px;
        }

        .logo2 {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 85px;
        }     

        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .text-10 { font-size: 10px; }
        .text-11 { font-size: 11px; }
        .text-14 { font-size: 14px; }
        .mt-5 { margin-top: 5px; }
        .mt-7 { margin-top: 7px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .top { vertical-align: top; }
        .bottom { vertical-align: bottom; }

        .tabla-sin-borde {
            width: 100%;
            border-collapse: collapse;
            line-height: 10px;
        }

        .tabla-datos { 
            width: 100%;
            border: 1px solid #000; 
            border-collapse: collapse;   
            line-height: 10px;  
        } 
        
        .tabla-datos td { 
            padding: 4px 6px;
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

        .tabla-extintores { 
            width: 80%;
            border: 1px solid #000; 
            border-collapse: collapse;   
            line-height: 8px;  
            font-size: 8px;
            text-align: center;
            margin-left: auto;
        } 
        
        .tabla-extintores th,
        .tabla-extintores td {
            border: 1px solid #000;
            padding: 4px;
        }

        .tabla-firmas {
            margin-top: 100px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
        }

        .tabla-firmas td {
            width: 25%;
            vertical-align: top;
        }

        .linea-firma {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto 5px auto;
        }

        .documento-firma {
            font-weight: bold;
            font-size: 11px;
        }

        .marcador-firma{
            color: white;
            font-size: 1px;
            line-height: 1px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            line-height: 11px;
        }

        .footer-note {
            margin-top: 5px;
            font-size: 9px;
            text-align: justify;
            line-height: 9px;
        }

        .footer-qr {
            position: absolute;
            bottom: 40px;
            right: 5px;
        }

        .footer-qr img {
            width: 80px;        
        }

        .qr-text {            
            color: red;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
    </style>
    <body>
        <div class="header">
            <img class="logo1" src="{{ asset('images/logo1.jpg') }}" alt="Logo">
            <img class="logo2" src="{{ asset('images/logo2.jpg') }}" alt="Logo">
            <div class="institucion">
                <div class="bold text-14" >{{ $institucion->razon_social }}</div>
                <div class="text-10">Teléfono: {{ $institucion->telefonos }} - email: {{ $institucion->email }}</div>
                <div class="text-10">Dirección: {{ $institucion->direccion }} - RUC: {{ $institucion->ruc }}</div>
                <div class="text-10">Ubicación: {{ $institucion->ubicacion }}</div>
            </div>

            <div class="text-center bold mt-20">
                FORMULARIO DE INSPECCIÓN VEHICULAR <span style="color: red;"># {{ $inspeccion->numero_formateado }}</span>
            </div>
        </div>

        <div id="content"> 
            <table class="tabla-datos text-11">
                <tr>
                    <td width="60%"><strong>Fecha:</strong> {{ strtoupper(\Carbon\Carbon::parse($inspeccion->fecha_inspeccion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                    <td width="40%"></td>
                </tr>
                <tr>
                    <td><strong>Inspector:</strong> {{ $inspeccion->nombre_personal }}</td>
                    <td><strong>Cod. Ext.:</strong>
                        @foreach ($inspeccion->procesoPermiso->extintores as $ext)
                            <strong>{{ str_pad($ext->numero, 3, '0', STR_PAD_LEFT) }}</strong>
                            @if (!$loop->last)
                                -
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
            <table class="tabla-datos mt-7 text-11">
                <tr>
                    <td colspan="3"><strong>VEHÍCULO</strong></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="45%"><strong>Placa:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->v_placa }}</td>
                    <td width="45%"><strong>Color:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->v_color }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td><strong>Marca:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->v_marca }}</td>
                    <td><strong>Cilindraje:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->v_cilindraje }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td><strong>Balde tipo:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->v_tipo }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2"><strong>Empresa / equipo:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->compania?->nombre }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2"><strong>Nombre del Propietario:</strong> {{ $inspeccion->procesoPermiso->cliente->nombre }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2"><strong>Dirección:</strong> {{ $inspeccion->procesoPermiso->cliente->direccion }}</td>
                </tr>
            </table>
            <table class="tabla-datos mt-7 text-11">
                <tr>
                    <td colspan="3"><strong>COMBUSTIBLE</strong></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="45%"><strong>Tipo de combustible:</strong> {{ $inspeccion->v_tipo_combustible }}</td>
                    <td width="45%"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><strong>Cantidad:</strong> {{ $inspeccion->v_cantidad_combustible }} Gls</td>
                    <td><strong>Cilindros:</strong> {{ $inspeccion->v_cantidad_cilindros }}</td>
                </tr>
            </table>
            <table class="tabla-datos mt-7 text-11">
                <tr>
                    <td colspan="3"><strong>TRANSPOTE</strong></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="45%"><strong>Origen:</strong> {{ $inspeccion->v_origen }}</td>
                    <td width="45%"><strong>Destino:</strong> {{ $inspeccion->v_destino }}</td>
                </tr>
            </table>
            <table class="tabla-datos mt-7 text-11">
                <tr>
                    <td colspan="4"><strong>NORMAS DE SEGURIDAD</strong></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">
                        <div style="line-height:10px;">
                            <strong>Extintores:</strong>
                            <p style="margin:5px 0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5LB 
                                <span class="check">{{ $inspeccion->v_capacidad_extintor == '5' ? 'X' : '' }}</span>
                            </p>
                            <p style="margin:5px 0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10LB 
                                <span class="check">{{ $inspeccion->v_capacidad_extintor == '10' ? 'X' : '' }}</span>
                            </p>
                            <p style="margin:5px 0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;20LB 
                                <span class="check">{{ $inspeccion->v_capacidad_extintor == '20' ? 'X' : '' }}</span>
                            </p>
                            <p style="margin:5px 0;"><strong>Tipo:</strong> {{ $inspeccion->v_tipo_extintor }}</p>
                        </div>
                    </td>
                    <td colspan="2" class="top">
                        <table class="tabla-extintores">
                            <tr style="background-color: #CBCBCB;">
                                <th rowspan="2">EXTINTORES</th>
                                <th rowspan="2">Cant. transp.</th>
                                <th colspan="2">Requisitos</th>
                            </tr>
                            <tr style="background-color: #CBCBCB;">
                                <th>#</th>
                                <th>Capacidad</th>
                            </tr>
                            <tr>
                                <td rowspan="2">TRANSPORTA CILINDROS<br>DE GLP</td>
                                <td>Hasta 100 cil.</td>
                                <td>1</td>
                                <td>10 lbs.</td>
                            </tr>
                            <tr>
                                <td>Mayor a 100 cil.</td>
                                <td>2</td>
                                <td>20 lbs.</td>
                            </tr>

                            <tr>
                                <td rowspan="2">TRANSPORTA LÍQUIDOS<br>COMBUSTIBLES</td>
                                <td>Hasta 110 Gal.</td>
                                <td>1</td>
                                <td>10 lbs.</td>
                            </tr>
                            <tr>
                                <td>Hasta 1000 gal.</td>
                                <td>2</td>
                                <td>20 lbs.</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Triángulos de seguridad</td>
                    <td width="30%">SI <span class="check {{ $inspeccion->v_triangulos_seguridad == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_triangulos_seguridad == 'SI' ? 'X' : '' }}</span></td>
                    <td width="30%">NO <span class="check {{ $inspeccion->v_triangulos_seguridad == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_triangulos_seguridad == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Guía Materiales peligrosos</td>
                    <td>SI <span class="check {{ $inspeccion->v_materiales_peligrosos == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_materiales_peligrosos == 'SI' ? 'X' : '' }}</span></td>
                    <td>NO <span class="check {{ $inspeccion->v_materiales_peligrosos == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_materiales_peligrosos == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Kit control derrames</td>
                    <td>SI <span class="check {{ $inspeccion->v_control_derrames == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_control_derrames == 'SI' ? 'X' : '' }}</span></td>
                    <td>NO <span class="check {{ $inspeccion->v_control_derrames == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_control_derrames == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Descarga a tierra</td>
                    <td>SI <span class="check {{ $inspeccion->v_descarga_tierra == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_descarga_tierra == 'SI' ? 'X' : '' }}</span></td>
                    <td>NO <span class="check {{ $inspeccion->v_descarga_tierra == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_descarga_tierra == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Arrestallamas</td>
                    <td>SI <span class="check {{ $inspeccion->v_arrestallamas == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_arrestallamas == 'SI' ? 'X' : '' }}</span></td>
                    <td>NO <span class="check {{ $inspeccion->v_arrestallamas == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_arrestallamas == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Señalización</td>
                    <td>SI <span class="check {{ $inspeccion->v_senalizacion == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_senalizacion == 'SI' ? 'X' : '' }}</span></td>
                    <td>NO <span class="check {{ $inspeccion->v_senalizacion == 'NO' ? 'X' : '' }}">{{ $inspeccion->v_senalizacion == 'NO' ? 'X' : '' }}</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Luces</td>
                    <td>Stop <span class="check {{ $inspeccion->v_luces_stop == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_luces_stop == 'SI' ? 'X' : '' }}</span></td>
                    <td>Parking <span class="check {{ $inspeccion->v_luces_parking == 'SI' ? 'X' : '' }}">{{ $inspeccion->v_luces_parking == 'SI' ? 'X' : '' }}</span></td>
                </tr>
            </table>
            <table class="tabla-datos mt-7 text-11">
                <tr class="top">
                    <td height="60px" class="text-justify"><strong>OBSERVACIONES:</strong> {{ $inspeccion->medidas_obligatorias }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <table class="tabla-sin-borde text-11" style="line-height: 10px; width:100%; ">
                <tr class="text-center">
                    <td width="33%">APROBADO <span class="check {{ $inspeccion->estado == 'APROBADO' ? 'X' : '' }}">{{ $inspeccion->estado == 'APROBADO' ? 'X' : '' }}</span></td>
                    <td width="33%">REINSPECCIÓN <span class="check {{ $inspeccion->estado == 'REINSPECCION' ? 'X' : '' }}">{{ $inspeccion->estado == 'REINSPECCION' ? 'X' : '' }}</span></td>
                    <td width="34%">NEGADO <span class="check {{ $inspeccion->estado != 'APROBADO' && $inspeccion->estado != 'REINSPECCION' ? 'X' : '' }}">{{ $inspeccion->estado != 'APROBADO' && $inspeccion->estado != 'REINSPECCION' ? 'X' : '' }}</span></td>
                </tr>
            </table>
            <table class="tabla-firmas mt-20">
                <tr>                    
                    <td>
                        {{-- Marcador para el microservicio --}}
                        <div class="marcador-firma">
                            __FIRMA_INSPECTOR__
                        </div>

                        <div class="linea-firma"></div>
                        <div>{{ ucwords(mb_strtolower($inspeccion->nombre_personal)) }}</div>
                        <div class="documento-firma">{{ $inspeccion->personal->documento }}</div>
                        <div>Prevención del B.C.B.S.: Carlos Olson y Av. Oriental</div>
                    </td>
                </tr>
            </table>
            <div class="footer-qr">
                <img class="qr" src="data:image/png;base64, {{ $qrBase64 }}">
                <div class="qr-text">
                    Verifique la validez del documento
                </div>
            </div>
        </div>
    </body>
</html>


