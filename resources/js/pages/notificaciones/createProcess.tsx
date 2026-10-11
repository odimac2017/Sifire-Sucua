import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { AlertTriangle, Bell, BellRing, Building2Icon, Calendar1Icon, Home, IdCard, Mail, MapPin, Phone, SaveIcon, SearchIcon, TriangleAlertIcon, Undo2Icon, User2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';
import TextAreaField from '@/components/form/TextAreaField';
import { Input } from '@/components/ui/input';
import { useEffect, useState } from 'react';
import SelectField from '@/components/form/SelectField';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import SearchableSelectField from '@/components/form/SearchableSelectField';
import DateField from '@/components/form/DateField';
import dayjs from 'dayjs';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear proceso de notificación',
        href: 'notificaciones/createProcess',
    },
];

interface TipoPermiso {
    id: number;
    nombre: string;
}

interface Tarifario {
    id: number;
    nombre: string;
    valor: number;
}

interface Personal {
    id: number;
    nombre: string;
}

interface Parroquia {
    id: number;
    nombre: string;
}

interface actividadEconomica {
    id: number,
    tipo_actividad: string,
    e_nombre_comercial: string,
    v_placa: string,
    v_marca: string,
    v_tipo: string,
    v_clase: string,
    v_color: string,
    fecha_inicio: string,
}

interface Cliente {
    id: number,
    documento: string,
    nombre: string,
    parroquia?: Parroquia;
    tipo_cliente_id: number;
    direccion: string,
    telefonos: string,
    email: string,
    artesano_calificado: number,
    documento_representante_legal: string;
    nombre_representante_legal: string;
    porcentaje_descuento: number,
    actividades_economicas?: actividadEconomica[],
}

interface FormData {
    cliente_id: number | null;
    tipo_cliente_id: number;
    actividad_economica_id: number | null;
    tipo_permiso_id: number | null;
    tarifario_id: number[];
    personal_id: number | null;
    numeroSolicitud: number;
    fecha_inspeccion: string;
    valor_impuesto_predial: number;
    artesano_calificado: number;
    porcentaje_descuento: number;
    fecha_inicio: string | null;     
    numeroBoleta: number;
    numero_notificacion: number;
    documento_representante_legal: string;
    nombre_representante_legal: string;
    documento_persona_recibe: string;
    nombre_persona_recibe: string;
    observaciones: string;
    year: number | null;
    search: string;
    estado: string; 
    searchCliente: string;
}

interface Props {
    tiposPermisos: TipoPermiso[];
    tarifarios: Tarifario[];
    personales: Personal[];
    cliente?: Cliente | null;    
    numeroSolicitud: number;
    numeroBoleta: number;
    filters: {
        year: number | null;
        search: string;
        estado: string;  
        searchCliente: string;      
    };
}

export default function Create({tiposPermisos, tarifarios, personales, cliente, numeroSolicitud, numeroBoleta, filters}: Props) {

    const actividadesEconomicas = cliente?.actividades_economicas ?? [];

    const actividadesOptions = actividadesEconomicas.map((actividad) => {
        let nombre = "";
    
        if (actividad.tipo_actividad === "E") {
            nombre = actividad.e_nombre_comercial;
        }
    
        if (actividad.tipo_actividad === "V") {
            nombre = `${actividad.v_placa} - ${actividad.v_marca} ${actividad.v_tipo} ${actividad.v_clase} ${actividad.v_color}`;
        }
    
        return {
            id: actividad.id,
            nombre: nombre,
            fecha_inicio: actividad.fecha_inicio,
        };
    });

    const [filtersState, setFiltersState] = useState({
        year: filters.year,
        search: filters.search || '',
        estado: filters.estado || '',
        searchCliente: filters.searchCliente || '',
    });

    const [loadingSearch, setLoadingSearch] = useState(false);

    const applyFilters = (newFilters?: Partial<typeof filtersState>) => {
        const updated = { ...filtersState, ...newFilters };
    
        setFiltersState(updated);
    
        setData("actividad_economica_id", null);
    
        setLoadingSearch(true);
    
        router.get('/notificaciones/createProcess', updated, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => setLoadingSearch(false),
        });
    };

    const { data, setData, post, processing, errors } = useForm<FormData>({
        cliente_id: cliente?.id ?? null,
        tipo_cliente_id: cliente?.tipo_cliente_id ?? 0,
        actividad_economica_id: null,
        tipo_permiso_id: null,
        tarifario_id: [],
        personal_id: null,
        numeroSolicitud: numeroSolicitud,
        fecha_inspeccion: dayjs().format("YYYY-MM-DD"),
        valor_impuesto_predial: 0,
        artesano_calificado: cliente?.artesano_calificado ?? 0,
        porcentaje_descuento: cliente?.porcentaje_descuento ?? 0,
        numeroBoleta: numeroBoleta,
        numero_notificacion: 1,
        documento_representante_legal: cliente?.documento_representante_legal ?? '',
        nombre_representante_legal: cliente?.nombre_representante_legal ?? '',
        documento_persona_recibe: '',
        nombre_persona_recibe: '',
        observaciones: '',
        fecha_inicio: null,
        year: filters.year,
        search: filters.search ?? '',
        estado: filters.estado ?? '',
        searchCliente: filters.searchCliente ?? '',
    });

    useEffect(() => {
        if (cliente?.id) {
            setData("cliente_id", cliente.id);
        }
    }, [cliente]);

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/notificaciones/storeProcess');
    };

    const tarifariosWithIcon = tarifarios?.map(group => ({
        ...group,
        icon: <Building2Icon className="w-4 h-4 mr-1 text-gray-500" />
    }));

    const [inspectorSugerido, setInspectorSugerido] = useState<any>(null);

    const sugerirInspector = async (fecha: string) => {
        try {
    
            const response = await fetch(`/solicitudesinspecciones/sugerirInspector?fecha=${fecha}`);
    
            if (!response.ok) {
                throw new Error("Error en servidor");
            }
    
            const inspector = await response.json();
    
            if (inspector) {
                setData("personal_id", inspector.id);
                setInspectorSugerido(inspector);
            }
    
        } catch (error) {
            console.error("Error al sugerir inspector", error);
        }
    };

    const [impuestoInput, setImpuestoInput] = useState("");

    const cargarUltimoProceso = async (actividadEconomicaId: number) => {
        try {
            const { data: response } = await axios.get(
                `/procesospermisos/${actividadEconomicaId}/ultimoProceso`
            );

            if (response) {
                setData("tipo_permiso_id", response.tipo_permiso_id);
                setData("tarifario_id", response.tarifarios);
            }
        } catch (error) {
            console.error(error);
        }
    };

    const numeroNotificacion = data.numero_notificacion;
    
    const config = {
        1: {
            texto: "PRIMERA NOTIFICACIÓN",
            color: "text-amber-600",
            bg: "bg-amber-50",
            icon: <Bell className="h-5 w-5 text-amber-600" />
        }
    };

    const estado = config[numeroNotificacion as 1];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear proceso de notificación" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 justify-end">
                    <div className="md:col-start-2">
                        <div className="flex items-start gap-3">
                            <div className="flex-1">
                                <div className="relative">
                                    <SearchIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                                    <Input
                                        type="text"
                                        className="pl-10"
                                        placeholder="Ingrese el documento del cliente"
                                        value={filtersState.searchCliente}
                                        onChange={(e) =>
                                            setFiltersState(prev => ({ ...prev, searchCliente: e.target.value }))
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === "Enter") {
                                                applyFilters();
                                            }
                                        }}
                                    />
                                </div>
                            </div>
                            <Button
                                disabled={processing}
                                type="button"
                                onClick={() => applyFilters()}
                            >
                                {!processing && <SearchIcon />}
                                {processing && <Spinner />}
                                Buscar
                            </Button>
                        </div>
                    </div>
                </div>
                {loadingSearch ? (
                    <div className="flex justify-center mt-6">
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <Spinner className="h-4 w-4" />
                            <span>Buscando cliente...</span>
                        </div>
                    </div>
                ) : cliente ? (
                    <form onSubmit={handleSubmit} className='space-y-4'>
                        <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className="flex justify-between items-start border-b pb-2 mb-3">
                                <div className="flex items-start gap-3 w-full">
                                    <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                        <User2Icon className="w-8 h-8 text-gray-700" />
                                    </div>
                                    <div className="w-full">
                                        <h1 className="text-lg font-semibold leading-tight">
                                            {cliente.nombre}
                                        </h1>

                                        <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                            <IdCard className="h-3.5 w-3.5" />
                                            <span>Documento:</span>
                                            <span className="font-medium text-foreground">
                                                {cliente.documento}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <p className="text-sm text-muted-foreground">Descuento</p>
                                    <p className="text-lg font-semibold text-red-600">
                                        {cliente.porcentaje_descuento}%
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                <Home className="h-3.5 w-3.5" />
                                <span>Dirección:</span>
                                <span className="font-medium text-foreground">{cliente.direccion}</span>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-muted-foreground text-sm mt-2">
                                <div className="flex items-start gap-2">
                                    <MapPin className="h-3.5 w-3.5" />
                                    <span>Parroquia:</span>
                                    <span className="font-medium text-foreground">{cliente.parroquia?.nombre}</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <Phone className="h-3.5 w-3.5" />
                                    <span>Teléfonos:</span>
                                    <span className="font-medium text-foreground">{cliente.telefonos}</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <Mail className="h-3.5 w-3.5" />
                                    <span>Email:</span>
                                    <span className="font-medium text-foreground">{cliente.email}</span>
                                </div>
                            </div>
                        </div> 
                        <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className="flex items-start gap-3 border-b pb-2 mb-3">
                                <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                    <Building2Icon className="w-8 h-8 text-gray-700" />
                                </div>
                                <div>
                                    <h1 className="text-lg font-semibold leading-tight">Actividades económicas</h1>
                                    <p className="text-sm text-muted-foreground">Seleccione la actividad económica para la inspección</p>
                                </div>
                            </div>
                            <div className="w-full">
                                <SelectField
                                    label="Actividad económica"
                                    name="actividad_economica_id"
                                    value={data.actividad_economica_id}
                                    options={actividadesOptions}
                                    error={errors.actividad_economica_id}
                                    onChange={(value) => {
                                        if (value === null) return;

                                        setData("actividad_economica_id", value);

                                        const actividadSeleccionada = actividadesOptions.find(
                                            p => p.id === value
                                        );

                                        if (actividadSeleccionada) {
                                            setData("fecha_inicio", actividadSeleccionada.fecha_inicio);
                                        }

                                        cargarUltimoProceso(value);
                                    }}
                                />
                            </div>
                        </div>
                        <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className="absolute top-3 right-4 bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                                <span className="text-xs opacity-80 mr-1">Solicitud de inspección</span>
                                <span className="font-bold tracking-wider">#{numeroSolicitud}-TEMP</span>
                            </div>
                            <div className="flex items-start gap-3 pb-2">
                                <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                    <Calendar1Icon className="w-8 h-8 text-gray-700" />
                                </div>
                                <div>
                                    <h1 className="text-lg font-semibold leading-tight">Fecha de solicitud</h1>
                                    <p className="text-sm text-muted-foreground">
                                        {new Date().toLocaleString('es-EC', {
                                            dateStyle: 'long',
                                            timeStyle: 'short'
                                        }).toUpperCase()}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                <div className="grid w-full gap-3">
                                    <DateField
                                        label="Fecha de inicio de actividades"
                                        value={data.fecha_inicio ?? undefined}
                                        onChange={(value) => setData("fecha_inicio", value)}
                                        error={errors.fecha_inicio}
                                        disableFuture                                
                                    />
                                </div> 
                                <div className="grid w-full gap-3">
                                    <SelectField
                                        label="Tipo de permiso a obtener"
                                        name="tipo_permiso_id"
                                        value={data.tipo_permiso_id}
                                        options={tiposPermisos}
                                        error={errors.tipo_permiso_id}
                                        uppercase={true}
                                        onChange={(value) => setData("tipo_permiso_id", Number(value) || null)}
                                    />
                                </div>
                                <div className="grid w-full gap-3 md:col-span-2">
                                    <SearchableSelectField
                                        label="Tarifarios"
                                        name="tarifario_id"
                                        multiple
                                        value={data.tarifario_id}
                                        options={tarifariosWithIcon ?? []}
                                        error={errors.tarifario_id}
                                        onChange={(value) => setData("tarifario_id", value as number[])}
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <DateField
                                        label="Fecha para inspección"
                                        value={data.fecha_inspeccion}
                                        onChange={(value) => {
                                            setData("fecha_inspeccion", value);
                                            sugerirInspector(value);
                                        }}
                                        error={errors.fecha_inspeccion}
                                        disablePast
                                    />
                                </div>                                
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Valor de impuesto predial"
                                        name="valor_impuesto_predial"
                                        value={impuestoInput}
                                        onChange={(e) => {
                                            const raw = e.target.value;

                                            if (/^\d*([.,]\d{0,3})?$/.test(raw)) {
                                                setImpuestoInput(raw);

                                                const numeric = parseFloat(raw.replace(",", "."));

                                                setData(
                                                    "valor_impuesto_predial",
                                                    isNaN(numeric) ? 0 : numeric
                                                );
                                            }
                                        }}
                                        error={errors.valor_impuesto_predial}
                                        placeholder="Especifique"
                                    />
                                </div>                                 
                            </div>
                        </div>
                        <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className="absolute top-3 right-4 bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                                <span className="text-xs opacity-80 mr-1">Boleta</span>
                                <span className="font-bold tracking-wider">#{numeroBoleta}-TEMP</span>
                            </div>
                            <div className="flex items-center gap-3 pb-2">
                                {estado.icon}
                                <span className={`font-semibold tracking-wide ${estado.color}`}>
                                    {estado.texto}
                                </span>
                            </div>
                        </div>
                        <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                {data.tipo_cliente_id === 2 && (   
                                    <>                                 
                                        <div className="grid gap-3">
                                            <InputField
                                                label="Documento del representante legal"
                                                name="documento_representante_legal"
                                                value={data.documento_representante_legal}
                                                onChange={e => setData('documento_representante_legal', e.target.value)}
                                                error={errors.documento_representante_legal}
                                                placeholder="Documento del representante legal"
                                            />
                                        </div>  
                                        <div className="grid gap-3">
                                            <InputField
                                                label="Nombre del representante legal"
                                                name="nombre_representante_legal"
                                                value={data.nombre_representante_legal}
                                                onChange={e => setData('nombre_representante_legal', e.target.value)}
                                                error={errors.nombre_representante_legal}
                                                placeholder="Nombre del representante legal"
                                            />
                                        </div>  
                                    </>                                   
                                )}                                 
                                <div className="grid gap-3">
                                    <InputField
                                        label="Documento de la pesonal que recibe la boleta"
                                        name="documento_persona_recibe"
                                        value={data.documento_persona_recibe}
                                        onChange={e => setData('documento_persona_recibe', e.target.value)}
                                        error={errors.documento_persona_recibe}
                                        placeholder="Especifique"
                                    />
                                </div>  
                                <div className="grid gap-3">
                                    <InputField
                                        label="Nombre de la pesonal que recibe la boleta"
                                        name="nombre_persona_recibe"
                                        value={data.nombre_persona_recibe}
                                        onChange={e => setData('nombre_persona_recibe', e.target.value)}
                                        error={errors.nombre_persona_recibe}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3 items-start">
                                    <SelectField
                                        label="Inspector asignado"
                                        name="personal_id"
                                        value={data.personal_id}
                                        options={personales}
                                        error={errors.personal_id}
                                        uppercase={true}
                                        onChange={(value) => setData("personal_id", Number(value) || null)}
                                    />
                                    {inspectorSugerido && (
                                        <p className="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-md mt-1 inline-block">
                                            {inspectorSugerido.total === 0 ? (
                                                <>
                                                    Inspector sugerido: Sin inspecciones asignadas aún
                                                </>
                                            ) : (
                                                <>
                                                    Inspector sugerido: 
                                                    <span className="font-semibold"> {inspectorSugerido.nombre}</span>
                                                    {" "}({inspectorSugerido.total}/{inspectorSugerido.limite} inspecciones)
                                                </>
                                            )}
                                        </p>
                                    )}
                                </div>
                                <div className='grid w-full gap-3 items-start md:col-span-2'> 
                                    <TextAreaField
                                        label="Observaciones"
                                        name="observaciones"
                                        value={data.observaciones}
                                        maxLength={300}
                                        placeholder="Especifique"
                                        error={errors.observaciones}
                                        onChange={value => setData('observaciones', value)}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                                {!processing && <SaveIcon />} {processing && <Spinner />} Crear proceso
                            </Button>                    
                            <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                                <Link 
                                    href='/notificaciones' 
                                    data={{ search: data.search, year: data.year, estado: data.estado }}
                                >
                                    <Undo2Icon /> Regresar
                                </Link>
                            </Button>
                        </div>  
                    </form>
                ) : filters.searchCliente ? (
                    <div className="flex justify-center my-6">
                        <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                            <TriangleAlertIcon className="h-4 w-4" />
                            <AlertTitle>No existen resultados</AlertTitle>
                            <AlertDescription className="text-destructive/80">
                                No se encontró ningún cliente con ese documento. Por favor intente de nuevo.
                            </AlertDescription>
                        </Alert>
                    </div>
                ) : (
                    <div className="flex justify-center mt-6">
                        <div className="flex items-center gap-3 border rounded-md bg-muted/30 shadow-sm p-4 text-sm text-muted-foreground w-full sm:w-1/2 lg:w-1/2">
                            <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                <SearchIcon className="w-5 h-5 text-gray-700" />
                            </div>
                            <div>
                                <p className="font-medium text-foreground">Busque un cliente</p>
                                <p className="text-muted-foreground">Ingrese el documento del cliente para iniciar la búsqueda.</p>
                            </div>
                        </div>
                    </div>             
                )}
            </div>
        </AppLayout>
    )
}