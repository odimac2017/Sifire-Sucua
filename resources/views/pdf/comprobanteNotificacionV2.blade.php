<!DOCTYPE>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comprobante de notificación #{{ $notificacion->numero_formateado }}</title>
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
                <p style="line-height: normal; text-align: center;"><b>COMPROBANTE No. {{$notificacion->numero_formateado}}</b></p>                
                <p style="line-height: normal; text-align: center;">
                    <b>
                        @if ($notificacion->numero_notificacion == 1)
                            PRIMERA NOTIFICACIÓN
                        @endif
                        @if ($notificacion->numero_notificacion == 2)
                            SEGUNDA NOTIFICACIÓN
                        @endif
                        @if ($notificacion->numero_notificacion == 3)
                            TERCERA NOTIFICACIÓN
                        @endif                
                    </b>
                </p>
                <p style="line-height: normal; font-size: 7px; text-align: justify;">El Cuerpo de Bomberos en uso de sus facultades, según el artículo 13 del Reglamento y artículos 32 y 35 de la Ley de Defensa Contra Incendios reformada, 
                    se le concede el termino de <b>@if ($notificacion->numero_notificacion === 1)
                        {{ $configuracion->plazo_primera_notificacion }}    
                    @elseif ($notificacion->numero_notificacion === 2)
                        {{ $configuracion->plazo_segunda_notificacion }}  
                    @else 
                        {{ $configuracion->plazo_tercera_notificacion }}  
                    @endif
                    DÍAS</b> contado(s) desde el dia de la notificación del presente documento, para que cumpla con la obtencion del Permiso de Funcionamiento del año <b>{{ \Carbon\Carbon::parse($notificacion->fecha_entrega)->year }}</b> según el artículo 359 del Reglamento de Prevención, Mitigación, y protección contra Incendios.</p>
                <p style="line-height: normal; margin-top:-2px;"><b>CC o RUC:</b> {{ $notificacion->procesoPermiso->cliente->documento }}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Cliente:</b> {{ $notificacion->procesoPermiso->cliente->nombre }}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Establecimiento:</b> {{ $notificacion->procesoPermiso->actividadEconomica->e_nombre_comercial }}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Representante legal:</b> {{ $notificacion->nombre_representante_legal }}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Dirección:</b> {{ $notificacion->procesoPermiso->cliente->direccion}}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Fecha de entrega:</b> {{ strtoupper(\Carbon\Carbon::parse($notificacion->fecha_entrega)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</p>
                <p style="line-height: normal; margin-top:-7px;"><b>Fecha plazo:</b> {{ strtoupper(\Carbon\Carbon::parse($notificacion->fecha_plazo)->translatedFormat('d \\d\\e F \\d\\e Y')) }}</p>
                @if (!empty($notificacion->observaciones))
                    <p style="line-height: normal; margin-top:-7px;"><b>Observaciones:</b> {{$notificacion->observaciones}}</p>
                @endif
                <p style="line-height: normal; margin-top:25px;">.................................................<br>{{ ucwords(mb_strtolower($notificacion->nombre_persona_recibe)) }}<br><b>{{ $notificacion->documento_persona_recibe }}</b></p>
                <p style="line-height: normal; margin-top:20px;">.................................................<br>{{ ucwords(mb_strtolower($notificacion->nombre_personal)) }}</p>
                <p style="line-height: normal; margin-top:-7px; text-align: right;">{{Carbon\Carbon::now()}}</p>
            </div>
        </div>
    </body>
</html>