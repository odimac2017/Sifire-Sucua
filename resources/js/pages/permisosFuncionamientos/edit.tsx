import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { BuildingIcon, CarIcon, Home, IdCard, Layers, Mail, MapPin, Phone, RefreshCcwIcon, SaveIcon, Undo2Icon, User2Icon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import InputField from '@/components/form/InputField';
import { money } from "@/utils/format";
import TextAreaField from '@/components/form/TextAreaField';
import DateField from '@/components/form/DateField';
import { useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar permiso de funcionamiento',
        href: '/permisosfuncionamientos/edit',
    },
];

interface Parroquia {
    id: number;
    nombre: string;
}

interface Cliente {
    id: number,
    documento: string,
    nombre: string,
    parroquia?: Parroquia;
    direccion: string,
    telefonos: string,
    email: string,
}

interface ActividadEconomica {
    id: number;
    tipo_actividad: string;
    e_nombre_comercial: string;
    e_actividad_principal: string;
    parroquia?: Parroquia;
    e_direccion: string;
    v_placa: string;
    v_marca: string;
    v_tipo: string;
    v_clase: string;
    v_color: string;
}

interface ProcesoPermisoTarifario {
    id: number;
    tarifario_id: number;
    valor_tarifario: number;
    valor_fijo: number;
    area_total: number;
    subtotal: number;
    tarifario: {
        nombre: string;
    };
}

interface ProcesoPermiso {
    id: number;
    cliente_id: number;
    actividad_economica_id: number;
    cliente: Cliente;
    actividad_economica: ActividadEconomica;
    tarifarios: ProcesoPermisoTarifario[];
    tipo_permiso: {
        tipo_inspeccion_id: number;
        nombre: string;
    }
}

interface PermisoFuncionamiento {
    id: number;
    proceso_permiso_id: number;
    numero_formateado: string;
    valor_inspeccion: number;
    valor_reinspeccion: number;
    valor_permiso: number;
    valor_inspecciones_anteriores: number;
    valor_total: number;
    fecha_expedicion: string;
    fecha_caducidad: string;
    fecha_deposito: string;
    documento_deposito: string;
    p_nombre_proyecto: string;
    p_nombre_profesional: string;
    p_area_total: number;
    observaciones: string;
}

interface Props {
    procesoPermiso: ProcesoPermiso;
    permisoFuncionamiento: PermisoFuncionamiento;
    filters: {
        year: number | null;
        search: string;
        estado: string;
    };
}

export default function Edit({procesoPermiso, permisoFuncionamiento, filters}: Props) {

    const cliente = procesoPermiso.cliente;
    const actividadEconomica = procesoPermiso.actividad_economica;

    const ES_ESTABLECIMIENTO = procesoPermiso.actividad_economica.tipo_actividad === "E";

    const [tarifariosLocal, setTarifariosLocal] = useState<ProcesoPermisoTarifario[]>([]);
    
    useEffect(() => {
        const tarifarios = procesoPermiso.tarifarios;

        if (Array.isArray(tarifarios)) {
            setTarifariosLocal(tarifarios);
        }
    }, [procesoPermiso.tarifarios]);

    const n = (v: any) => Number(v ?? 0);

    const [areaInput, setAreaInput] = useState(
        permisoFuncionamiento.p_area_total
            ? String(permisoFuncionamiento.p_area_total)
            : ""
    );

    const {data, setData, put, processing, errors, transform} = useForm({
        proceso_permiso_id: permisoFuncionamiento.proceso_permiso_id,
        numero_formateado: permisoFuncionamiento.numero_formateado,
        valor_inspeccion: permisoFuncionamiento.valor_inspeccion,
        valor_reinspeccion: permisoFuncionamiento.valor_reinspeccion,
        valor_permiso: permisoFuncionamiento.valor_permiso,
        valor_inspecciones_anteriores: permisoFuncionamiento.valor_inspecciones_anteriores,
        valor_total: permisoFuncionamiento.valor_total,
        fecha_expedicion: permisoFuncionamiento.fecha_expedicion,
        fecha_caducidad: permisoFuncionamiento.fecha_caducidad,
        fecha_deposito: permisoFuncionamiento.fecha_deposito,
        documento_deposito: permisoFuncionamiento.documento_deposito,
        p_nombre_proyecto: permisoFuncionamiento.p_nombre_proyecto,
        p_nombre_profesional: permisoFuncionamiento.p_nombre_profesional,
        p_area_total: permisoFuncionamiento.p_area_total,
        observaciones: permisoFuncionamiento.observaciones,
        search: filters?.search ?? '',
        year: filters.year ?? new Date().getFullYear(),
        estado: filters?.estado ?? '',
    });

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        transform((current) => ({
            ...current,
            proceso_permiso: {
                tarifarios: tarifariosLocal,
            },
        }));

        put(`/permisosfuncionamientos/${permisoFuncionamiento.id}`);
    }

    const calcularSubtotalTarifa = (tarifa: ProcesoPermisoTarifario) => {

        const area = n(tarifa.area_total);
        const valor = n(tarifa.valor_tarifario);

        return n(valor) * n(area);
    };

    useEffect(() => {
        if (procesoPermiso.tipo_permiso.tipo_inspeccion_id !== null) return;

        const total = tarifariosLocal.reduce((acc, tarifa) => {
            return acc + n(tarifa.subtotal ?? 0);
        }, 0);

        setData('valor_total', total);

    }, [tarifariosLocal]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar permiso de funcionamiento" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                    <div className="flex justify-between items-start border-b pb-2 mb-3">
                        <div className="flex items-start gap-3 w-full">
                            <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                <User2Icon className="w-8 h-8 text-gray-700" />
                            </div>
                            <div className="w-full">
                                <h1 className="text-lg font-semibold leading-tight">{cliente.nombre}</h1>
                                <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                    <IdCard className="w-4 h-4" />
                                    <span>Documento:</span>
                                    <span className="font-medium text-foreground">{cliente.documento}</span>
                                </div>
                            </div>
                        </div>    
                        <div className="md:col-span-2 flex gap-2 justify-end">                            
                            <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                                <Link 
                                    href={`/permisosfuncionamientos`}
                                    data={{ search: data.search, year: data.year, estado: data.estado }}
                                >
                                    <Undo2Icon /> Regresar
                                </Link>
                            </Button>                             
                        </div>
                    </div>
                    <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                        <Home className="w-4 h-4" />
                        <span>Dirección:</span>
                        <span className="font-medium text-foreground">{cliente.direccion}</span>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-muted-foreground text-sm mt-2">
                        <div className="flex items-start gap-2">
                            <MapPin className="w-4 h-4" />
                            <span>Parroquia:</span>
                            <span className="font-medium text-foreground">{cliente.parroquia?.nombre}</span>
                        </div>
                        <div className="flex items-start gap-2">
                            <Phone className="w-4 h-4" />
                            <span>Teléfonos:</span>
                            <span className="font-medium text-foreground">{cliente.telefonos}</span>
                        </div>
                        <div className="flex items-start gap-2">
                            <Mail className="w-4 h-4" />
                            <span>Email:</span>
                            <span className="font-medium text-foreground">{cliente.email}</span>
                        </div>
                    </div>
                </div>
                <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                    <div className="flex justify-between items-start border-b pb-2 mb-3">
                        <div>
                            {ES_ESTABLECIMIENTO ? (
                                <>  
                                    <div className="flex items-start gap-3 w-full">
                                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                            <BuildingIcon className="w-8 h-8 text-gray-700" />
                                        </div>
                                        <div className="w-full">
                                            <h1 className="text-lg font-semibold leading-tight">{actividadEconomica.e_nombre_comercial}</h1>
                                            <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                                {actividadEconomica.e_actividad_principal}
                                            </div>
                                        </div>
                                    </div>
                                </>
                            ) : (
                                <>  
                                    <div className="flex items-start gap-3 w-full">
                                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                            <CarIcon className="w-8 h-8 text-gray-700" />
                                        </div>
                                        <div className="w-full">
                                            <h1 className="text-lg font-semibold leading-tight">{actividadEconomica.v_placa}</h1>
                                            <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                                {actividadEconomica.v_marca} {actividadEconomica.v_tipo} {actividadEconomica.v_clase} {actividadEconomica.v_color}
                                            </div>
                                        </div>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-muted-foreground text-sm mt-2">
                        <div className="flex items-start gap-2 md:col-span-2">
                            <Home className="w-4 h-4" />
                            <span>Dirección:</span>
                            <span className="font-medium text-foreground">{actividadEconomica.e_direccion}</span>
                        </div>                    
                        <div className="flex items-start gap-2 md:col-span-1">
                            <MapPin className="w-4 h-4" />
                            <span>Parroquia:</span>
                            <span className="font-medium text-foreground">{actividadEconomica.parroquia?.nombre}</span>
                        </div>
                    </div>
                </div>
                <form onSubmit={handleUpdate} className='space-y-4'>
                    <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                        <div className="flex justify-end items-start mb-4">                            
                            <div className="bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                                <span className="text-xs opacity-80 mr-1 capitalize">{procesoPermiso.tipo_permiso?.nombre}</span>
                                <span className="font-bold tracking-wider">#{data.numero_formateado}</span>
                            </div>
                        </div>
                        <Card className="rounded-xl shadow-sm mb-4">
                            <CardContent className="space-y-3">
                                {procesoPermiso.tipo_permiso.tipo_inspeccion_id === null && (
                                    tarifariosLocal.map((tarifa, index) => (
                                        <>
                                            <div
                                                key={tarifa.id ?? index}
                                                className="rounded-xl border bg-card text-card-foreground shadow-sm"
                                            >
                                                <div className="p-4 space-y-4">        
                                                    <div className="flex items-start justify-between">
                                                        <div className="space-y-0.5">
                                                            <p className="text-sm font-medium">
                                                                {tarifa.tarifario.nombre}
                                                            </p>
                                                        </div>
                                                        <div className="text-right">
                                                            <Badge className="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-50">
                                                                ${n(tarifa.valor_tarifario).toFixed(2)}
                                                            </Badge>
                                                        </div>
                                                    </div>
                                                    <div className="h-px bg-border" />
                                                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                        <span className="text-sm font-medium text-muted-foreground">
                                                            Subtotal
                                                        </span>
                                                        <div className="text-right">
                                                            <Badge className="text-md bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50">
                                                                ${(calcularSubtotalTarifa(tarifa) ?? 0).toFixed(2)}
                                                            </Badge>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                        
                                        </>
                                    ))
                                )}
                                {procesoPermiso.tipo_permiso.tipo_inspeccion_id != null && (
                                    <>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600">
                                                Valor de la inspección técnica
                                            </span>
                                            <div className="flex items-center gap-1">
                                                <span className="font-semibold text-blue-700 dark:text-blue-400 tabular-nums">
                                                    {money(data.valor_inspeccion)}
                                                </span>
                                                <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                    USD
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600">
                                                Valor de la reinspección técnica
                                            </span>
                                            <div className="flex items-center gap-1">
                                                <span className="font-semibold text-blue-700 dark:text-blue-400 tabular-nums">
                                                    +{money(data.valor_reinspeccion)}
                                                </span>
                                                <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                    USD
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600">
                                                Valor del permiso
                                            </span>
                                            <div className="flex items-center gap-1">
                                                <span className="font-semibold text-blue-600 dark:text-blue-400 tabular-nums">
                                                    +{money(data.valor_permiso)}
                                                </span>
                                                <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                    USD
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600">
                                                Valor de inspecciones anteriores sin permisos
                                            </span>
                                            <div className="flex items-center gap-1">
                                                <span className="font-semibold text-red-600 dark:text-red-400 tabular-nums">
                                                    +{money(data.valor_inspecciones_anteriores)}
                                                </span>
                                                <span className="text-xs text-muted-foreground ml-1">
                                                    USD
                                                </span>
                                            </div>
                                        </div>
                                    </>
                                )}
                                <div className="border-t pt-3 flex justify-between text-base font-semibold">
                                    <span className="text-gray-600">
                                        Valor total
                                    </span>
                                    <div className="flex items-center gap-1">
                                        <Badge className="px-3 py-2 text-md bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50">
                                            <span className="font-semibold tabular-nums">
                                                {money(data.valor_total)}
                                            </span>
                                            <span className="text-xs text-muted-foreground ml-1">
                                                USD
                                            </span>
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <div className='grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 items-start'>
                            {procesoPermiso.tipo_permiso.tipo_inspeccion_id === null && (
                                <>
                                    <div className='grid w-full gap-3 items-start md:col-span-2'>
                                        <InputField
                                            label="Nombre del proyecto"
                                            name="p_nombre_proyecto"
                                            value={data.p_nombre_proyecto}
                                            onChange={e => setData('p_nombre_proyecto', e.target.value)}
                                            error={errors.p_nombre_proyecto}
                                            placeholder="Especifique"
                                        />
                                    </div>
                                    <div className='grid w-full gap-3 items-start'>
                                        <InputField
                                            label="Nombre del profesional"
                                            name="p_nombre_profesional"
                                            value={data.p_nombre_profesional}
                                            onChange={e => setData('p_nombre_profesional', e.target.value)}
                                            error={errors.p_nombre_profesional}
                                            placeholder="Especifique"
                                        />
                                    </div>
                                    <div className="grid w-full gap-3">
                                        <InputField
                                            label="Área total del proyecto"
                                            name="p_area_total"
                                            value={areaInput}
                                            onChange={(e) => {
                                                const raw = e.target.value;

                                                // permitir solo números, punto o coma
                                                if (!/^[0-9.,]*$/.test(raw)) return;

                                                setAreaInput(raw);

                                                const numeric = parseFloat(raw.replace(",", "."));

                                                if (!isNaN(numeric)) {
                                                    setData("p_area_total", numeric);

                                                    setTarifariosLocal(prev =>
                                                        prev.map((t) => {
                                                            const updated = {
                                                                ...t,
                                                                area_total: numeric,
                                                            };

                                                            updated.subtotal = calcularSubtotalTarifa(updated);
                                                            return updated;
                                                        })
                                                    );
                                                }
                                            }}
                                            error={errors.p_area_total}
                                            placeholder="Especifique"
                                        />
                                    </div>
                                    <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                        <Layers className="w-4 h-4" />
                                        <span className="text-sm font-semibold">
                                            
                                        </span>
                                        <div className="flex-1 h-px bg-border ml-2"></div>
                                    </div>
                                </>
                            )}               
                            <div className="grid w-full gap-3">
                                <DateField
                                    label="Fecha de expedicion"
                                    value={data.fecha_expedicion}
                                    onChange={(value) => setData("fecha_expedicion", value)}
                                    error={errors.fecha_expedicion}
                                    maxDate={data.fecha_caducidad}
                                />
                            </div>
                            {procesoPermiso.tipo_permiso.tipo_inspeccion_id !== null && (
                                <div className="grid w-full gap-3">
                                    <DateField
                                        label="Fecha de caducidad"
                                        value={data.fecha_caducidad}
                                        onChange={(value) => setData("fecha_caducidad", value)}
                                        error={errors.fecha_caducidad}                                        
                                        minDate={data.fecha_expedicion}
                                    />
                                </div>
                            )}
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 items-start'>                        
                            <div className="grid w-full gap-3">
                                <DateField
                                    label="Fecha de depósito"
                                    value={data.fecha_deposito}
                                    onChange={(value) => setData("fecha_deposito", value)}
                                    error={errors.fecha_deposito}
                                    disableFuture
                                />
                            </div>  
                            <div className="grid w-full gap-3">
                                <InputField
                                    label="Documento de depósito"
                                    name="documento_deposito"
                                    value={data.documento_deposito}
                                    onChange={e => setData('documento_deposito', e.target.value)}
                                    error={errors.documento_deposito}
                                    placeholder="Especifique"
                                />
                            </div>
                        </div>
                        <div className='grid w-full gap-3 items-start'> 
                            <TextAreaField
                                label="Observaciones"
                                name="observaciones"
                                value={data.observaciones}
                                maxLength={1000}
                                placeholder="Especifique"
                                error={errors.observaciones}
                                onChange={value => setData('observaciones', value)}
                            />
                        </div>
                    </div>
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar permiso
                        </Button>    
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href={`/permisosfuncionamientos`}
                                data={{ search: data.search, year: data.year, estado: data.estado }}
                            >
                                <Undo2Icon /> Regresar
                            </Link>
                        </Button>
                    </div> 
                </form>             
            </div>
        </AppLayout>
    );    
}