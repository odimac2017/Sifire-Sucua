<!DOCTYPE>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informe de Inspección #{{ $inspeccion->numero_formateado }}</title>
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
        }

        .tabla-datos { 
            border: 1px solid #000; 
            border-collapse: collapse;     
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

        .tabla-firmas {
            margin-top: 60px;
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
            width: 185px;
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

            <div class="text-center bold mt-5">
                JEFATURA DE PREVENCIÓN DE INCENDIOS
            </div>

            <table class="tabla-sin-borde mt-20 text-14">
                <tr>
                    <td width="33%" class="text-left bold">INFORME DE INSPECCIÓN <span class="check {{ $inspeccion->procesoPermiso->solicitudInspeccion->tipo_solicitud == 'IN' ? 'X' : '' }}">{{ $inspeccion->procesoPermiso->solicitudInspeccion->tipo_solicitud == 'IN' ? 'X' : '' }}</span></td>
                    <td width="33%" class="text-center bold">INFORME DE REINSPECCIÓN <span class="check {{ $inspeccion->procesoPermiso->solicitudInspeccion->tipo_solicitud == 'RE' ? 'X' : '' }}">{{ $inspeccion->procesoPermiso->solicitudInspeccion->tipo_solicitud == 'RE' ? 'X' : '' }}</span></td>
                    <td width="33%" class="text-right bold"><span style="color: red;"># {{ $inspeccion->numero_formateado }}</span></td>
                </tr>
            </table>
        </div>

        <div id="content">            
            <table class="tabla-datos text-11" style="line-height: 10px; width: 78%;">
                <tr>
                    <td><strong>Nombre:</strong> {{ $inspeccion->procesoPermiso->cliente->nombre }}</td>
                    <td><strong>Predio:</strong> 
                        {{ $inspeccion->e_tipo_funcion == 'P' ? 'PROPIETARIO' : ($inspeccion->e_tipo_funcion == 'A' ? 'ARRENDATARIO' : '') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Razón social:</strong> {{ $inspeccion->procesoPermiso->actividadEconomica->e_nombre_comercial }}</td>
                </tr>
                <tr>
                    <td><strong>CC o RUC:</strong> {{ $inspeccion->procesoPermiso->cliente->documento }}</td>
                    <td><strong>Fecha:</strong> {{ strtoupper(\Carbon\Carbon::parse($inspeccion->fecha_inspeccion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong> {{ $inspeccion->procesoPermiso->cliente->direccion }}</td>
                    <td><strong>Referencia:</strong> {{ $inspeccion->procesoPermiso->cliente->sector }}</td>
                </tr>
                <tr>
                    <td><strong>Materiales que almacena:</strong> {{ $inspeccion->e_materiales_almacena }}</td>
                    <td><strong>Teléfonos:</strong> {{ $inspeccion->procesoPermiso->cliente->telefonos }}</td>
                </tr>
            </table>        
            <table class="tabla-datos mt-7 text-11" style="line-height: 10px; width: 78%;">
                <tr>
                    <td colspan="6"><strong>RIESGO ESTRUCTURAL</strong></td>
                </tr>
                <tr class="bottom">
                    <td>Hormigón armado <span class="check {{ $inspeccion->e_rgo_estr_hormigon == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_hormigon == '1' ? 'X' : '' }}</span></td>
                    <td>Madera <span class="check {{ $inspeccion->e_rgo_estr_madera == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_madera == '1' ? 'X' : '' }}</span></td>
                    <td>Mixta <span class="check {{ $inspeccion->e_rgo_estr_mixta == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_mixta == '1' ? 'X' : '' }}</span></td>
                    <td>Estructura metálica <span class="check {{ $inspeccion->e_rgo_estr_metalica == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_metalica == '1' ? 'X' : '' }}</span></td>
                    <td>Otros: {{$inspeccion->e_rgo_estr_otros}} </td>
                    <td></td>
                </tr>
                <tr class="bottom">
                    <td>Cubierta metálica <span class="check {{ $inspeccion->e_rgo_estr_cub_metalica == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_metalica == '1' ? 'X' : '' }}</span></td>
                    <td>Madera <span class="check {{ $inspeccion->e_rgo_estr_cub_madera == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_madera == '1' ? 'X' : '' }}</span></td>
                    <td>Tumbado <span class="check {{ $inspeccion->e_rgo_estr_cub_tumbado == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_tumbado == '1' ? 'X' : '' }}</span></td>
                    <td>Fibrcemento <span class="check {{ $inspeccion->e_rgo_estr_cub_fibrocemento == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_fibrocemento == '1' ? 'X' : '' }}</span></td>
                    <td>Playwood <span class="check {{ $inspeccion->e_rgo_estr_cub_playwood == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_playwood == '1' ? 'X' : '' }}</span></td>
                    <td>Otros <span class="check {{ $inspeccion->e_rgo_estr_cub_otros == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_cub_otros == '1' ? 'X' : '' }}</span></td>
                </tr>
                <tr class="bottom">
                    <td>N° pisos: <strong>{{$inspeccion->e_rgo_estr_num_pisos}}</strong></td>
                    <td colspan="2">Piso que ocupa: <strong>{{$inspeccion->e_rgo_estr_piso_ocupa}}</strong></td>
                    <td colspan="2">Área del local: <strong>{{$inspeccion->e_rgo_estr_area_local}} m<sup>2</sup></strong></td>
                    <td></td>
                </tr>
                <tr class="bottom">
                    <td>N° surtidores:</td>
                    <td colspan="2">Diésel: <strong>{{$inspeccion->e_rgo_estr_surt_diesel}}</strong></td>
                    <td colspan="2">Gasolina: <strong>{{$inspeccion->e_rgo_estr_surt_gasolina}}</strong></td>
                    <td></td>
                </tr>
                <tr class="bottom">
                    <td>Cap. almacenamiento:</td>
                    <td colspan="2">Diésel: <strong>{{$inspeccion->e_rgo_estr_cap_diesel}} Gls</strong></td>
                    <td>Gasolina: <strong>{{$inspeccion->e_rgo_estr_cap_gasolina}} Gls</strong></td>
                    <td>Gas: <strong>{{$inspeccion->e_rgo_estr_cap_gas}}</strong></td>
                    <td></td>
                </tr>
                <tr class="bottom">
                    <td colspan="3">Tratamiento ingnifugo (RF 120)
                        &nbsp;&nbsp;&nbsp;SI <span class="check {{ $inspeccion->e_rgo_estr_trat_ingnifugo == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_trat_ingnifugo == '1' ? 'X' : '' }}</span>
                        &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_rgo_estr_trat_ingnifugo == '0' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_trat_ingnifugo == '0' ? 'X' : '' }}</span>
                    </td>
                    <td>Piso Antideslisante <span class="check {{ $inspeccion->e_rgo_estr_piso_antideslisante == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_piso_antideslisante == '1' ? 'X' : '' }}</td>
                    <td>Deslisante <span class="check {{ $inspeccion->e_rgo_estr_piso_deslisante == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_piso_deslisante    == '1' ? 'X' : '' }}</td>
                    <td>Otros <span class="check {{ $inspeccion->e_rgo_estr_piso_otro == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_estr_piso_otro == '1' ? 'X' : '' }}</td>
                </tr>
                <tr class="bottom">
                    <td colspan="2">Cumple Norma INEN: <strong>{{$inspeccion->e_rgo_estr_norma_inen}}</strong></td>
                    <td colspan="4" class="text-justify">Observaciones: <strong>{{$inspeccion->e_rgo_estr_observaciones}}</strong></td>
                </tr>
            </table>        
            <table class="tabla-datos mt-7 text-11" style="line-height: 10px; width: 78%;">
                <tr>
                    <td colspan="5"><strong>RIESGOS ELÉCTRICOS</strong></td>
                </tr>
                <tr class="bottom">
                    <td>Buenas <span class="check {{ $inspeccion->e_rgo_elect_buenas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_buenas == '1' ? 'X' : '' }}</td>
                    <td>Defectuosas <span class="check {{ $inspeccion->e_rgo_elect_defectuosas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_defectuosas == '1' ? 'X' : '' }}</td>
                    <td>Malas <span class="check {{ $inspeccion->e_rgo_elect_malas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_malas == '1' ? 'X' : '' }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="bottom">
                    <td>Empotradas <span class="check {{ $inspeccion->e_rgo_elect_empotradas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_empotradas == '1' ? 'X' : '' }}</td>
                    <td>Sobrepuestas <span class="check {{ $inspeccion->e_rgo_elect_sobrepuestas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_sobrepuestas == '1' ? 'X' : '' }}</td>
                    <td>Voltaje 110V <span class="check {{ $inspeccion->e_rgo_elect_volt_110 == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_volt_110 == '1' ? 'X' : '' }}</td>
                    <td>220V <span class="check {{ $inspeccion->e_rgo_elect_volt_220 == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_volt_220 == '1' ? 'X' : '' }}</td>
                    <td>Industrial <span class="check {{ $inspeccion->e_rgo_elect_volt_industrial == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_volt_industrial == '1' ? 'X' : '' }}</td>
                </tr>
                <tr class="bottom">
                    <td>Caja de protección eléctrica</td>
                    <td>
                        SI <span class="check {{ $inspeccion->e_rgo_elect_protec_electrica == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_protec_electrica == '1' ? 'X' : '' }}</span>
                        &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_rgo_elect_protec_electrica == '0' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_protec_electrica == '0' ? 'X' : '' }}</span>
                    </td>
                    <td>Transformadores</td>
                    <td colspan="2">
                        SI <span class="check {{ $inspeccion->e_rgo_elect_transformadores == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_transformadores == '1' ? 'X' : '' }}</span>
                        &nbsp;&nbsp;&nbsp;<strong>{{$inspeccion->e_rgo_elect_trans_kva}} KVA</strong>
                        &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_rgo_elect_transformadores == '0' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_transformadores == '0' ? 'X' : '' }}</span>
                    </td>
                </tr>
                <tr class="bottom">
                    <td>Generadores <span class="check {{ $inspeccion->e_rgo_elect_generadores == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_generadores == '1' ? 'X' : '' }}</td>
                    <td>Eléctricos <span class="check {{ $inspeccion->e_rgo_elect_electricos == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_elect_electricos == '1' ? 'X' : '' }}</td>
                    <td colspan="3">Combutible: <strong>{{ $inspeccion->e_rgo_elect_combustible }}</strong></td>
                </tr>
            </table>        
            <table class="tabla-datos mt-7 text-11" style="line-height: 10px; width: 78%;">
                <tr>
                    <td colspan="6"><strong>RIESGOS A LA SALUD (EPP)</strong></td>
                </tr>
                <tr class="bottom">
                    <td>Casco <span class="check {{ $inspeccion->e_rgo_salud_epp_casco == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_casco == '1' ? 'X' : '' }}</td>
                    <td>Gafas <span class="check {{ $inspeccion->e_rgo_salud_epp_gafas == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_gafas == '1' ? 'X' : '' }}</td>
                    <td>Calzado <span class="check {{ $inspeccion->e_rgo_salud_epp_calzado == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_calzado == '1' ? 'X' : '' }}</td>
                    <td>Vestimenta <span class="check {{ $inspeccion->e_rgo_salud_epp_vestimenta == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_vestimenta == '1' ? 'X' : '' }}</td>
                    <td>Guantes <span class="check {{ $inspeccion->e_rgo_salud_epp_guantes == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_guantes == '1' ? 'X' : '' }}</td>
                    <td>Orejeras <span class="check {{ $inspeccion->e_rgo_salud_epp_orejeras == '1' ? 'X' : '' }}">{{ $inspeccion->e_rgo_salud_epp_orejeras == '1' ? 'X' : '' }}</td>
                </tr>
                <tr class="top">
                    <td colspan="6">Observaciones: <strong>{{ $inspeccion->e_rgo_salud_observaciones }}</strong></td>
                </tr> class="text-justify"
            </table>        
            <table class="tabla-datos mt-7 text-11" style="line-height: 10px; width: 78%;">
                <tr class="top">
                    <td height="60px" class="text-justify"><strong>MEDIDAS OBLIGATORIAS QUE DEBE CUMPLIR:</strong> {{ $inspeccion->medidas_obligatorias }}</td>
                </tr>
            </table>
            <div style="position:absolute; top:103; left:433;">   
                <table class="tabla-datos text-11" style="line-height: 11.8px; width:98%">
                    <tr class="text-center">
                        <td><strong>SISTEMA DE DEFENSA CONTRA INCENDIOS</strong></td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Extintor<br>
                            PQS <span class="check {{ $inspeccion->e_sis_incend_pqs == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_pqs == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;CO2 <span class="check {{ $inspeccion->e_sis_incend_co2 == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_co2 == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;H2O <span class="check {{ $inspeccion->e_sis_incend_h2o == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_h2o == '1' ? 'X' : '' }}</span>
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Detector de humo<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_det_humo == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_det_humo == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_det_humo == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_det_humo == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Lámpara de Emergencia<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_lamp_emer == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_lamp_emer == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_lamp_emer == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_lamp_emer == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Gabinente<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_gabinete == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_gabinete == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_gabinete == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_gabinete == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Splinklers<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_sprinklers == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_sprinklers == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_sprinklers == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_sprinklers == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Alarma de Incendio<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_alarm_incendio == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_alarm_incendio == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_alarm_incendio == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_alarm_incendio == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Bomba Hidráulica<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_bomb_hidraulica == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_bomb_hidraulica == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_bomb_hidraulica == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_bomb_hidraulica == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Siamesa<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_siamesa == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_siamesa == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_siamesa == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_siamesa == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Escalera de Emergencia<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_escalera_emer == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_escalera_emer == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_escalera_emer == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_escalera_emer == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Puertas Corta Fuego<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_puert_corta_fuego == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_puert_corta_fuego == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_puert_corta_fuego == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_puert_corta_fuego == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Señalización de Emergencia<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_senal_emer == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_senal_emer == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_senal_emer == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_senal_emer == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Puertas de Emergencia<br>
                            SI <span class="check {{ $inspeccion->e_sis_incend_puert_emer == '1' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_puert_emer == '1' ? 'X' : '' }}</span>
                            &nbsp;&nbsp;&nbsp;NO <span class="check {{ $inspeccion->e_sis_incend_puert_emer == '0' ? 'X' : '' }}">{{ $inspeccion->e_sis_incend_puert_emer == '0' ? 'X' : '' }}</span>                                    
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            Metros Cúbicos<br>
                            <strong>{{ $inspeccion->e_sis_incend_cap_tanq_reserv }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
            <table class="tabla-sin-borde mt-7 text-11" style="line-height: 10px;">
                <tr>
                    <td class="text-justify">CÓDIGO DE EXINTORES: 
                        @foreach ($inspeccion->procesoPermiso->extintores as $ext)
                            <strong>{{ str_pad($ext->numero, 3, '0', STR_PAD_LEFT) }}</strong>
                            @if (!$loop->last)
                                -
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
            <div class="footer">
                <table class="tabla-sin-borde text-11" style="line-height: 10px; width:100%; ">
                    <tr class="text-center">
                        <td width="50%">APROBADO <span class="check {{ $inspeccion->estado == 'APROBADO' ? 'X' : '' }}">{{ $inspeccion->estado == 'APROBADO' ? 'X' : '' }}</span></td>
                        <td width="50%">REINSPECCIÓN <span class="check {{ $inspeccion->estado == 'REINSPECCION' ? 'X' : '' }}">{{ $inspeccion->estado == 'REINSPECCION' ? 'X' : '' }}</span></td>
                    </tr>
                </table>
                <table class="tabla-tarifas text-11 mt-5">
                    <tr>
                        <th colspan="14">CUADRO DE CÁLCULO</th>
                    </tr>
                    <tr>
                        <th width="7%" rowspan="2">CÓDIGO</th>
                        <th colspan="3">NIVEL DE RIESGO</th>
                        <th colspan="4">ÁREA DEL LOCAL</th>
                        <th colspan="3">RECURSOS NECESARIOS</th>
                        <th colspan="3">UBICACIÓN</th>
                    </tr>
                    <tr>
                        <th width="7%">ALTO</th>
                        <th width="7%">MEDIO</th>
                        <th width="7%">BAJO</th>
                        <th width="7%">GRANDE</th>
                        <th width="7%">MEDIA</th>
                        <th width="7%">PEQUEÑO</th>
                        <th width="7%">MÍNIMO</th>
                        <th width="7%">ESPECIAL</th>
                        <th width="7%">MEDIO</th>
                        <th width="7%">MÍNIMO</th>
                        <th width="7%">CANTÓN</th>
                        <th width="7%">PARROQUIA</th>
                        <th width="7%">RURAL</th>
                    </tr>
                    @foreach ($inspeccion->procesoPermiso->tarifarios as $tarifa)
                        <tr class="text-center">
                            <td>{{ $tarifa->tarifario->codigo }}</td>
                            <td><strong>{{ $tarifa->calc_nivel_riesgo_item?->value == 'AL' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_nivel_riesgo_item?->value == 'ME' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_nivel_riesgo_item?->value == 'BA' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_local_item?->value == 'GR' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_local_item?->value == 'ME' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_local_item?->value == 'PE' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_local_item?->value == 'MI' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_recursos_item?->value == 'ES' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_recursos_item?->value == 'ME' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_recursos_item?->value == 'MI' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_ubicacion_item?->value == 'CA' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_ubicacion_item?->value == 'PA' ? 'X' : '' }}</strong></td>
                            <td><strong>{{ $tarifa->calc_ubicacion_item?->value == 'RU' ? 'X' : '' }}</strong></td>
                        </tr>
                    @endforeach
                </table>
                <div class="footer-note">
                    IMPORTANTE: Art. 35 de la Codificación de la Ley de Defensa contra Incendios y Art. 40 del Reglamento General para la aplicación de la Ley de Defensa Contra Incendios.<br>
                    Dispone que los Jefes de los Cuerpos de Bomberos podrán conceder permisos anuales y ocasionales, clausura de locales y todas las medidas necesarias para prevenir flagelos y sancionar las violaciones a esta Ley. 
                    <u>Tiene 8 días de plazo a partir de esa fecha (Según Art. 356 del Reglamento de Prevención, Mitigación y Protección contra Incendios).</u><br>
                    <b>Art. 26 Contravención cuarta clase, numeral 8:</b> Los que se opusieren a las inspecciones ordenadas por el Cuerpo de Bomberos en su morada o en inmuebles de su propiedad o tenencia.
                </div>
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
        </div>        
    </body>
</html>


