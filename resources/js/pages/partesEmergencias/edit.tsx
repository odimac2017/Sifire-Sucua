import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { CheckCheckIcon, LifeBuoy, MapPinIcon, PlusIcon, RefreshCcwIcon, Trash2, TriangleAlertIcon, Undo2Icon } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import React, { useEffect, useState } from 'react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import MapPicker from '@/components/MapPicker';
import InputField from '@/components/form/InputField';
import axios from 'axios';
import SearchableSelectField from '@/components/form/SearchableSelectField';
import DateField from '@/components/form/DateField';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import RichTextEditor from '@/components/form/RichTextEditor';
import { useDuracion } from "@/hooks/useDuracion";
import { Input } from '@/components/ui/input';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar parte de emergencia',
        href: 'partesemergencias/edit',
    },
];

interface Categoria {
    id: number;
    principal: string;
    nombre: string;
}

interface Provincia {
    id: number;
    nombre: string;
}

interface Canton {
    id: number;
    nombre: string;
}

interface Parroquia {
    id: number;
    nombre: string;
}

interface Personal {
    id: number;
    nombre: string;
}

interface PersonalPuesto {
    id: number;
    nombre: string;
}

interface Insumo {
    id: number;
    nombre: string;
    presentacion: string;
}

interface PersonalParte {
    personal_id: number
    nombre_personal?: string
}

interface PersonalQapParte {
    personal_id: number
    nombre_personal?: string
    desde: string
    hasta: string
    tiempo_total: string
}

interface InsumoParte {
    insumo_id: number
    cantidad: number
}

interface ParteEmergencia {
    id: number,
    categoria_emergencia_id: number;
    provincia_id: number;
    canton_id: number;
    parroquia_id: number;
    personal_radio_id: number;    
    numero: number;
    secuencia: string;
    inicial: boolean;
    seguimiento: boolean;
    final: boolean;
    fecha_llamada: string;
    fecha_llegada: string;
    fecha_atencion: string;
    direccion: string;       
    fuente: string;
    contexto_general: string;
    personal_externo: string;
    ver_geolocalizacion: number;
    latitud: number | null;
    longitud: number | null;    
}

interface Props {
    categorias: Categoria[];
    provincias: Provincia[];
    personales: Personal[];
    personalesPuestos: PersonalPuesto[];
    insumos: Insumo[];
    parteEmergencia: ParteEmergencia;
    categoriasParte: number[];
    personalesParte: number[],
    personalesQapParte: PersonalQapParte[];        
    insumosParte: InsumoParte[];
    filters: {
        search: string;
        year: number;
    };
}

export default function Edit({categorias, provincias, personales, personalesPuestos, insumos, parteEmergencia, categoriasParte, personalesParte, personalesQapParte, insumosParte, filters}: Props) {

    const {data, setData, put, processing, errors} = useForm({
        categoria_emergencia_id: categoriasParte,
        provincia_id: parteEmergencia.provincia_id,
        canton_id: parteEmergencia.canton_id,
        parroquia_id: parteEmergencia.parroquia_id,
        personal_radio_id: parteEmergencia.personal_radio_id,
        numero: parteEmergencia.numero,
        secuencia: parteEmergencia.secuencia,
        inicial: parteEmergencia.inicial,
        seguimiento: parteEmergencia.seguimiento,
        final: parteEmergencia.final,
        fecha_llamada: parteEmergencia.fecha_llamada,
        fecha_llegada: parteEmergencia.fecha_llegada,
        fecha_atencion: parteEmergencia.fecha_atencion,
        direccion: parteEmergencia.direccion,
        fuente: parteEmergencia.fuente,
        contexto_general: parteEmergencia.contexto_general,        
        personal_externo: parteEmergencia.personal_externo,
        ver_geolocalizacion: parteEmergencia.ver_geolocalizacion,
        latitud: parteEmergencia.latitud,
        longitud: parteEmergencia.longitud,
        personalesParte: personalesParte ?? [],
        personalesQapParte: personalesQapParte ?? [],
        insumosParte: insumosParte ?? [],  
        search: filters?.search ?? '',
        year: filters.year,
    });

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/partesemergencias/${parteEmergencia.id}`);
    }

    const [cantones, setCantones] = useState<Canton[]>([]);
    const [parroquias, setParroquias] = useState<Parroquia[]>([]);

    useEffect(() => {
        if (!data.provincia_id) {
            setCantones([]);
            setParroquias([]);
            return;
        }
    
        const controller = new AbortController();
    
        axios.get(`/cantones/obtenerCanton/${data.provincia_id}`, {
            signal: controller.signal
        })
        .then(res => setCantones(res.data))
        .catch(() => setCantones([]));
    
        return () => controller.abort();
    }, [data.provincia_id]);

    useEffect(() => {
        if (!data.canton_id) {
            setParroquias([]);
            return;
        }
    
        const controller = new AbortController();
    
        axios.get(`/parroquias/obtenerParroquia/${data.canton_id}`, {
            signal: controller.signal
        })
        .then(res => setParroquias(res.data))
        .catch(() => setParroquias([]));
    
        return () => controller.abort();
    }, [data.canton_id]);

    const duracionTexto = useDuracion(
        data.fecha_llegada,
        data.fecha_atencion
    );

    const getCurrentLocation = () => {
        if (!navigator.geolocation) {
            alert('La geolocalización no está soportada por este navegador.');
            return;
        }
    
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
    
                setData('latitud', latitude);
                setData('longitud', longitude);
            },
            (error) => {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        alert('Permiso de ubicación denegado.');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert('Ubicación no disponible.');
                        break;
                    case error.TIMEOUT:
                        alert('Tiempo de espera agotado.');
                        break;
                    default:
                        alert('Error al obtener la ubicación.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    };

    const [personalQapSeleccionado, setPersonalQapSeleccionado] = useState<number | null>(null);    
    
    const obtenerFechaActual = () => {
        const ahora = new Date()
        const offset = ahora.getTimezoneOffset()
        const local = new Date(ahora.getTime() - offset * 60000)
        return local.toISOString().slice(0, 16)
    }

    const [fechaDesde, setFechaDesde] = useState(obtenerFechaActual());
    const [fechaHasta, setFechaHasta] = useState(obtenerFechaActual());

    const [mensajePersonalQap, setMensajePersonalQap] = useState<{
        texto: string;
        tipo: "success" | "error";
    } | null>(null);

    const handleAgregarPersonalQap = () => {

        if (!personalQapSeleccionado) {
            setMensajePersonalQap({
                texto: "Debe seleccionar un personal para agregar",
                tipo: "error"
            });
            return;
        }

        if (!fechaDesde || !fechaHasta) {
            setMensajePersonalQap({
                texto: "Debe seleccionar fecha y hora",
                tipo: "error"
            })
            return
        }

        const inicio = new Date(fechaDesde)
        const fin = new Date(fechaHasta)

        if (fin <= inicio) {
            setMensajePersonalQap({
                texto: "La fecha hasta debe ser mayor que desde",
                tipo: "error"
            })
            return
        }
    
        if (data.personalesQapParte.some(p => p.personal_id === personalQapSeleccionado)) {
            setMensajePersonalQap({
                texto: "El personal ya fue agregado",
                tipo: "error"
            });
            return;
        }
    
        const diff = fin.getTime() - inicio.getTime()

        const horas = Math.floor(diff / (1000 * 60 * 60))
        const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
        const segundos = Math.floor((diff % (1000 * 60)) / 1000)

        const tiempo_total = `${String(horas).padStart(2, "0")}:${String(minutos).padStart(2, "0")}:${String(segundos).padStart(2, "0")}`

        const nuevoRegistro = {
            personal_id: personalQapSeleccionado,
            desde: fechaDesde,
            hasta: fechaHasta,
            tiempo_total
        }

        setData("personalesQapParte", [
            ...data.personalesQapParte,
            nuevoRegistro
        ])

        setMensajePersonalQap({
            texto: "Personal agregado correctamente",
            tipo: "success"
        })

        setPersonalQapSeleccionado(null);
        setFechaDesde(obtenerFechaActual());
        setFechaHasta(obtenerFechaActual());
    }

    useEffect(() => {
        if (!mensajePersonalQap) return;

        const timer = setTimeout(() => {
            setMensajePersonalQap(null);
        }, 3000);

        return () => clearTimeout(timer);
    }, [mensajePersonalQap]);    

    const [mensajeInsumo, setMensajeInsumo] = useState<{
        texto: string;
        tipo: "success" | "error";
    } | null>(null);

    const [insumoSeleccionado, setInsumoSeleccionado] = useState<number | null>(null);

    const [cantidad, setCantidad] = useState(1);

    const handleAgregarInsumo = () => {

        if (!insumoSeleccionado) {
            setMensajeInsumo({
                texto: "Debe seleccionar un insumo para agregar",
                tipo: "error"
            });
            return;
        }
            
        if (data.insumosParte.some(p => p.insumo_id === insumoSeleccionado)) {
            setMensajeInsumo({
                texto: "El insumo ya fue agregado",
                tipo: "error"
            });
            return;
        }  

        const nuevoRegistro = {
            insumo_id: insumoSeleccionado,
            cantidad: cantidad
        }

        setData("insumosParte", [
            ...data.insumosParte,
            nuevoRegistro
        ])

        setMensajeInsumo({
            texto: "Insumo agregado correctamente",
            tipo: "success"
        })

        setInsumoSeleccionado(null);
        setCantidad(1);
    }

    useEffect(() => {
        if (!mensajeInsumo) return;

        const timer = setTimeout(() => {
            setMensajeInsumo(null);
        }, 3000);

        return () => clearTimeout(timer);
    }, [mensajeInsumo]);   

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar parte de emergencia" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">    
                    <div className="flex items-center justify-between">        
                        <div className="flex items-center gap-3">
                            <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                <LifeBuoy className="w-8 h-8 text-gray-700" />
                            </div>
                            <h1 className="text-lg font-semibold leading-tight">
                                Registro oficial del incidente
                            </h1>
                        </div>
                        <div className="bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                            <span className="text-xs opacity-80 mr-1">Parte de emergencia</span>
                            <span className="font-bold tracking-wider">#{data.numero}</span>
                        </div>
                    </div>
                </div>
                <Tabs defaultValue="parte" className="w-full">
                    <TabsList variant="line">
                        <TabsTrigger value="parte">Parte de emergencia</TabsTrigger>
                        <TabsTrigger value="geolocalizacion">Geolocalización</TabsTrigger>
                    </TabsList>
                    <TabsContent value="parte">
                        <Card>
                            <CardContent>
                                <form onSubmit={handleUpdate} className='space-y-4'>
                                    <div className='grid w-full gap-3 items-start'>
                                        <InputField
                                            label="Secuencia"
                                            name="secuencia"
                                            value={data.secuencia ?? ''}
                                            onChange={e => setData('secuencia', e.target.value)}
                                            error={errors.secuencia}
                                            placeholder="Especifique"
                                        />
                                    </div>                                 
                                    <div className='grid w-full gap-3 items-start'>
                                        <SearchableSelectField
                                            label="Categorías"
                                            name="categoria_emergencia_id"
                                            value={data.categoria_emergencia_id}
                                            multiple
                                            options={categorias ?? []}
                                            error={errors.categoria_emergencia_id}
                                            onChange={(value) => setData("categoria_emergencia_id", value as number[])}
                                        />
                                    </div>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Tipo de evento
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-3 gap-4">
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="inicial"
                                                    checked={data.inicial}
                                                    onCheckedChange={(checked) =>
                                                        setData('inicial', checked)
                                                    }
                                                />
                                                <Label htmlFor="inicial">Inicial</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="seguimiento"
                                                    checked={data.seguimiento}
                                                    onCheckedChange={(checked) =>
                                                        setData('seguimiento', checked)
                                                    }
                                                />
                                                <Label htmlFor="seguimiento">Seguimiento</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="final"
                                                    checked={data.final}
                                                    onCheckedChange={(checked) =>
                                                        setData('final', checked)
                                                    }
                                                />
                                                <Label htmlFor="final">Final</Label>
                                            </div>                                            
                                        </CardContent>
                                    </Card>
                                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>                        
                                        <div className="grid w-full gap-3">
                                            <DateField
                                                label="Fecha y hora de llamada"
                                                value={data.fecha_llamada}
                                                onChange={(value) => setData("fecha_llamada", value)}
                                                error={errors.fecha_llamada}                                                
                                                withTime
                                                maxDate={data.fecha_atencion}
                                            />
                                        </div>
                                        <div className="grid w-full gap-3">
                                            <DateField
                                                label="Fecha y hora de llegada"
                                                value={data.fecha_llegada}
                                                onChange={(value) => setData("fecha_llegada", value)}
                                                error={errors.fecha_llegada}                                                
                                                withTime
                                                maxDate={data.fecha_atencion}
                                            />
                                        </div>  
                                        <div className="space-y-1">
                                            <div className="flex items-center justify-between">
                                                <span className="text-sm font-medium">Fecha y hora de atención</span>
                                                {duracionTexto && (
                                                    <span className="text-xs text-muted-foreground font-semibold">
                                                        Tiempo de atención: {duracionTexto}
                                                    </span>
                                                )}
                                            </div>
                                            <DateField
                                                label=""
                                                value={data.fecha_atencion}
                                                onChange={(value) => setData("fecha_atencion", value)}
                                                error={errors.fecha_atencion}                                                
                                                withTime
                                                minDate={data.fecha_llegada}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                        <div className="grid w-full gap-3">
                                            <SearchableSelectField
                                                label="Provincia"
                                                name="provincia_id"
                                                value={data.provincia_id}
                                                options={provincias ?? []}
                                                error={errors.provincia_id}
                                                onChange={(value) => setData("provincia_id", Number(value))}
                                            />
                                        </div>
                                        <div className="grid w-full gap-3 items-start">
                                            <SearchableSelectField
                                                label="Cantón"
                                                name="canton_id"
                                                value={data.canton_id}
                                                options={cantones ?? []}
                                                error={errors.canton_id}
                                                onChange={(value) => setData("canton_id", Number(value))}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>                        
                                        <div className="grid w-full gap-3">
                                            <SearchableSelectField
                                                label="Parroquia"
                                                name="parroquia_id"
                                                value={data.parroquia_id}
                                                options={parroquias ?? []}
                                                error={errors.parroquia_id}
                                                onChange={(value) => setData("parroquia_id", Number(value))}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid w-full gap-3 items-start'>
                                        <InputField
                                            label="Dirección"
                                            name="direccion"
                                            value={data.direccion ?? ''}
                                            onChange={e => setData('direccion', e.target.value)}
                                            error={errors.direccion}
                                            placeholder="Dirección"
                                        />
                                    </div>                                    
                                    <div className='grid w-full gap-3 items-start'>
                                        <InputField
                                            label="Fuente"
                                            name="fuente"
                                            value={data.fuente}
                                            onChange={e => setData('fuente', e.target.value)}
                                            error={errors.fuente}
                                            placeholder="Especifique"
                                        />
                                    </div>
                                    <div className='grid w-full gap-3 items-start'>
                                        <RichTextEditor
                                            label="Contexto general"
                                            value={data.contexto_general}
                                            onChange={(value) => setData('contexto_general', value)}
                                            error={errors.contexto_general}
                                        />
                                    </div>
                                    <div className="grid w-full gap-3 items-start">
                                        <SearchableSelectField
                                            label="Personal de respuesta"
                                            name="personalesParte"
                                            multiple
                                            value={data.personalesParte}
                                            options={personales ?? []}
                                            error={errors.personalesParte}
                                            onChange={(value) => setData("personalesParte", value as number[])}
                                        />
                                    </div>
                                    <div className='grid w-full gap-3 items-start'>
                                        <SearchableSelectField
                                            label="Radio operador"
                                            name="personal_radio_id"
                                            value={data.personal_radio_id}
                                            options={personales ?? []}
                                            error={errors.personal_radio_id}
                                            onChange={(value) => setData("personal_radio_id", Number(value))}
                                        />
                                    </div>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Personal de QAP (Apoyo en emergencia)
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="w-full">
                                                <div className="flex items-center gap-3">
                                                    <Label htmlFor="personalQap" className="whitespace-nowrap">
                                                        Personal
                                                    </Label>
                                                    <SearchableSelectField
                                                        name="personalQap"
                                                        value={personalQapSeleccionado ?? null}
                                                        options={personales}
                                                        onChange={(value) => setPersonalQapSeleccionado(value ? Number(value) : null)}
                                                    />
                                                    <div className="flex items-center gap-3">
                                                        <Label className="whitespace-nowrap">Desde</Label>
                                                        <Input
                                                            type="datetime-local"
                                                            value={fechaDesde}
                                                            onChange={(e) => setFechaDesde(e.target.value)}
                                                        />
                                                        <Label className="whitespace-nowrap">Hasta</Label>
                                                        <Input
                                                            type="datetime-local"
                                                            value={fechaHasta}
                                                            onChange={(e) => setFechaHasta(e.target.value)}
                                                        />
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        className="bg-slate-500 hover:bg-slate-700 h-9 cursor-pointer"
                                                        onClick={handleAgregarPersonalQap}
                                                    >
                                                        <PlusIcon className="h-4 w-4" />
                                                        Agregar
                                                    </Button>
                                                </div>
                                                {mensajePersonalQap && (
                                                    <Alert                                        
                                                        className={`mt-4 border-none w-full px-4 py-2
                                                        ${
                                                            mensajePersonalQap.tipo === "error"
                                                            ? "bg-red-500/10 text-red-600"
                                                            : "bg-green-500/10 text-green-600"
                                                        }
                                                        `}
                                                    >
                                                        {mensajePersonalQap.tipo === "error" ? (
                                                            <TriangleAlertIcon className="h-4 w-4" />
                                                        ) : (
                                                            <CheckCheckIcon className="h-4 w-4" />
                                                        )}
                                                        <AlertTitle>{mensajePersonalQap.texto}</AlertTitle>
                                                    </Alert>
                                                )}
                                            </div>
                                            {data.personalesQapParte.length ? (
                                                <div className="overflow-x-auto mt-4">
                                                    <table className="w-full border overflow-hidden rounded-md text-sm">
                                                        <thead>
                                                            <tr className="bg-muted">
                                                                <th className="py-2 px-4 text-left border-b">#</th>
                                                                <th className="py-2 px-4 text-left border-b">Nombre</th>
                                                                <th className="py-2 px-4 text-left border-b">Desde</th>
                                                                <th className="py-2 px-4 text-left border-b">Hasta</th>
                                                                <th className="py-2 px-4 text-left border-b">Total</th>
                                                                <th className="py-2 px-4 text-left border-b"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {data.personalesQapParte.map((personal, index) => {
                                                                const personalObj = personales?.find(p => p.id === personal.personal_id);

                                                                return (
                                                                    <tr className="odd:bg-muted/50 hover:bg-gray-50" key={index}>
                                                                        <td className="py-2 px-4 border-b">{index + 1}</td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            {personalObj?.nombre ?? "No encontrado"}
                                                                        </td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            {dayjs(personal.desde).tz('America/Guayaquil').format('YYYY-MM-DD HH:mm')}
                                                                        </td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            {dayjs(personal.hasta).tz('America/Guayaquil').format('YYYY-MM-DD HH:mm')}
                                                                        </td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            {personal.tiempo_total}
                                                                        </td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            <Button
                                                                                type="button"
                                                                                size="sm"
                                                                                variant="destructive"
                                                                                className="cursor-pointer"
                                                                                onClick={() =>
                                                                                    setData(
                                                                                        "personalesQapParte",
                                                                                        data.personalesQapParte.filter(
                                                                                            p => p.personal_id !== personal.personal_id
                                                                                        )
                                                                                    )
                                                                                }
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </Button>
                                                                        </td>
                                                                    </tr>
                                                                );
                                                            })}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ) : (
                                                <div className="flex justify-center mt-6">
                                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                                        <TriangleAlertIcon className="h-4 w-4" />
                                                        <AlertTitle>Este parte de emergencia no cuenta con personal QAP</AlertTitle>
                                                        <AlertDescription className="text-destructive/80">
                                                            Por favor agregue un nuevo personal.
                                                        </AlertDescription>
                                                    </Alert>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                    <div className='grid w-full gap-3 items-start'>
                                        <RichTextEditor
                                            label="Personal de apoyo externo"
                                            value={data.personal_externo}
                                            onChange={(value) => setData('personal_externo', value)}
                                            error={errors.personal_externo}
                                        />
                                    </div>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Insumos utilizados
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="w-full">
                                                <div className="flex items-center gap-3">
                                                    <Label htmlFor="insumo" className="whitespace-nowrap">
                                                        Insumo
                                                    </Label>
                                                    <SearchableSelectField
                                                        name="insumo"
                                                        value={insumoSeleccionado ?? null}
                                                        options={insumos}
                                                        onChange={(value) => setInsumoSeleccionado(value ? Number(value) : null)}
                                                    />
                                                    <div className="flex items-center gap-3">
                                                        <Label className="whitespace-nowrap">Cantidad</Label>
                                                        <Input
                                                            type="text"
                                                            value={cantidad}
                                                            onChange={(e) => setCantidad(Number(e.target.value))}
                                                        />                                                        
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        className="bg-slate-500 hover:bg-slate-700 h-9 cursor-pointer"
                                                        onClick={handleAgregarInsumo}
                                                    >
                                                        <PlusIcon className="h-4 w-4" />
                                                        Agregar
                                                    </Button>
                                                </div>
                                                {mensajeInsumo && (
                                                    <Alert                                        
                                                        className={`mt-4 border-none w-full px-4 py-2
                                                        ${
                                                            mensajeInsumo.tipo === "error"
                                                            ? "bg-red-500/10 text-red-600"
                                                            : "bg-green-500/10 text-green-600"
                                                        }
                                                        `}
                                                    >
                                                        {mensajeInsumo.tipo === "error" ? (
                                                            <TriangleAlertIcon className="h-4 w-4" />
                                                        ) : (
                                                            <CheckCheckIcon className="h-4 w-4" />
                                                        )}
                                                        <AlertTitle>{mensajeInsumo.texto}</AlertTitle>
                                                    </Alert>
                                                )}
                                            </div>
                                            {data.insumosParte.length ? (
                                                <div className="overflow-x-auto mt-4">
                                                    <table className="w-full border overflow-hidden rounded-md text-sm">
                                                        <thead>
                                                            <tr className="bg-muted">
                                                                <th className="py-2 px-4 text-left border-b">#</th>
                                                                <th className="py-2 px-4 text-left border-b">Nombre</th>
                                                                <th className="py-2 px-4 text-left border-b">Presentación</th>
                                                                <th className="py-2 px-4 text-left border-b">Cantidad</th>
                                                                <th className="py-2 px-4 text-left border-b"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {data.insumosParte.map((insumo, index) => {
                                                                const insumoObj = insumos?.find(p => p.id === insumo.insumo_id);

                                                                return (
                                                                    <tr className="odd:bg-muted/50 hover:bg-gray-50" key={index}>
                                                                        <td className="py-2 px-4 border-b">{index + 1}</td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            {insumoObj?.nombre ?? "No encontrado"}
                                                                        </td>   
                                                                        <td className="py-2 px-4 border-b">
                                                                            {insumoObj?.presentacion ?? "No encontrado"}
                                                                        </td>                                                                     
                                                                        <td className="py-2 px-4 border-b">
                                                                            {insumo.cantidad}
                                                                        </td>
                                                                        <td className="py-2 px-4 border-b">
                                                                            <Button
                                                                                type="button"
                                                                                size="sm"
                                                                                variant="destructive"
                                                                                className="cursor-pointer"
                                                                                onClick={() =>
                                                                                    setData(
                                                                                        "insumosParte",
                                                                                        data.insumosParte.filter(
                                                                                            p => p.insumo_id !== insumo.insumo_id
                                                                                        )
                                                                                    )
                                                                                }
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </Button>
                                                                        </td>
                                                                    </tr>
                                                                );
                                                            })}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ) : (
                                                <div className="flex justify-center mt-6">
                                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                                        <TriangleAlertIcon className="h-4 w-4" />
                                                        <AlertTitle>Este parte de emergencia no cuenta con insumos</AlertTitle>
                                                        <AlertDescription className="text-destructive/80">
                                                            Por favor agregue un nuevo insumo.
                                                        </AlertDescription>
                                                    </Alert>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>                                    
                                    <div className="flex gap-2">                           
                                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar parte
                                        </Button>                    
                                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                                            <Link 
                                                href='/partesemergencias' 
                                                data={{ search: data.search, year: data.year }}
                                            >
                                                <Undo2Icon /> Regresar
                                            </Link>
                                        </Button>
                                    </div> 
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="geolocalizacion">
                        <Card>
                            <CardContent className="space-y-4">                                                          
                                <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                    <div className="grid w-full gap-3">
                                        <InputField
                                            label="Latitud"
                                            name="latitud"
                                            type='latitud'
                                            value={data.latitud ?? ''}
                                            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                setData(
                                                    'latitud',
                                                    e.target.value === '' ? null : Number(e.target.value)
                                                )
                                            }
                                            error={errors.latitud}
                                            placeholder="Ej: -0.180653"
                                        />
                                    </div>
                                    <div className='grid w-full gap-3 items-start'>
                                        <InputField
                                            label="Longitud"
                                            name="longitud"
                                            type='longitud'
                                            value={data.longitud ?? ''}
                                            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                setData(
                                                    'longitud',
                                                    e.target.value === '' ? null : Number(e.target.value)
                                                )
                                            }
                                            error={errors.longitud}
                                            placeholder="Ej: -78.467834"
                                        />
                                    </div>
                                </div>
                                <MapPicker
                                    lat={data.latitud}
                                    lng={data.longitud}
                                    onChange={(lat, lng) => {
                                        setData('latitud', lat);
                                        setData('longitud', lng);
                                    }}
                                />
                                <div className="flex items-center justify-between gap-3">
                                    <p className="text-sm text-muted-foreground">
                                        Haz clic en el mapa para seleccionar la ubicación.
                                    </p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={getCurrentLocation}
                                        className="flex items-center gap-2"
                                    >
                                        <MapPinIcon size={16} />
                                        Usar mi ubicación actual
                                    </Button>      
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>                
            </div>
        </AppLayout>
    )
}
