<!DOCTYPE>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comprobante de Inspección #{{ $inspeccion->numero_formateado }}</title>
    <style>   
        @page {
            margin-top: 1em;
            margin-left: 1em;
            margin-right: 1em;
            margin-bottom: 0em;
        }

        body{
            font-family: sans-serif;
            line-height : 18px;
        }
    </style>
    <body>
        <section>
            <div style="position:absolute; text-align: center; font-size:9px; top:-8; width: 100%;">
                <p style="line-height:10px;"><b>{{$institucion->razon_social}}</b></p>
                <p style="line-height:0px;"><b>Dirección:</b> {{$institucion->direccion}}</p>
                <p style="line-height:0px;"><b>Teléfonos:</b> {{$institucion->telefonos}}</p>
            </div>
            <div style="position:absolute; font-size:9px; top:28; width: 100%;">
                <p style="line-height: normal; text-align: center;"><b>COMPROBANTE DE INSPECCIÓN No. {{$inspeccion->numero_formateado}}</b></p>
                <p style="line-height: normal;"><b>Establecimiento:</b><br>{{$inspeccion->procesoPermiso->actividadEconomica->e_nombre_comercial}}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Dirección:</b><br>{{$inspeccion->procesoPermiso->actividadEconomica->e_direccion}}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Fecha de inspección:</b><br>{{ strtoupper(\Carbon\Carbon::parse($inspeccion->fecha_inspeccion)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</p>
                <p style="line-height: normal; margin-top:-7px; font-size: 7px;"><i>Tiene 8 días de plazo a partir de esta fecha (Según el Art. 356 del Reglamento de Prevención, Mitigación y Protección contra Incendios)</i></p>
                <p style="line-height: normal; margin-top:-7px;"><b>Estado de la inspección:</b><p>
                <p style="line-height: 0px; margin-top:-5px; text-align: center; font-size: 11px;"><b>
                    @if ($inspeccion->estado == 'APROBADO')
                        APROBADO
                    @endif
                    @if ($inspeccion->estado == 'REINSPECCION')
                        SUJETO A REINSPECCIÓN
                    @endif
                    @if ($inspeccion->estado == 'NOTIFICACION')
                        PARA ETAPA DE NOTIFICACIÓN
                    @endif
                </b></p>
                @if ($inspeccion->estado == 'APROBADO')
                    <p style="line-height: normal; margin-top:-7px;"><b>Valor a cancelar:</b></p>
                    <p style="line-height: normal; margin-top:-10px; text-align: center; font-size: 12px;"><b>US. $ {{ $inspeccion->valor_total }}</b></p>
                    <p style="line-height: normal; margin-top:-8px; text-align: left;"><b>SUBLÍNEA: 130112 PERMISOS</b></p>
                @endif
                <p style="line-height: normal; margin-top:-7px;"><b>Medidas obligatorias a cumplir:</b><br>{{$inspeccion->medidas_obligatorias}}</p>
                <p style="line-height: normal; margin-top:25px;">.................................................<br>{{ ucwords(mb_strtolower($inspeccion->procesoPermiso->cliente->nombre)) }}</p>
                <p style="line-height: normal; margin-top:-20px; text-align: right;"><b>({{$inspeccion->procesoPermiso->cliente->archivo_fisico}})</b></p>
                <p style="line-height: normal; margin-top:20px;">.................................................<br>{{ ucwords(mb_strtolower($inspeccion->nombre_personal)) }}</p>
                <p style="line-height: normal; margin-top:-7px; text-align: right;">{{Carbon\Carbon::now()}}</p>
            </div>
        </section>
    </body>
</html>