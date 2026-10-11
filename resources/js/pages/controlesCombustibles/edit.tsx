import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { Calendar1Icon, RefreshCcwIcon, SaveIcon, Undo2Icon } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { Card, CardContent } from "@/components/ui/card";
import InputField from '@/components/form/InputField';
import DateField from '@/components/form/DateField';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { money } from "@/utils/format";
import SelectField from '@/components/form/SelectField';
import NumberInput from '@/components/form/NumberInput';
import { Badge } from '@/components/ui/badge';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar control de combustible',
        href: 'controlescombustibles/edit',
    },
];

interface Bien {
    id: number;
    tipo_bien: string;
    identificacion: string;
    placa: string;
    clase_vehiculo: string;
    tipo_vehiculo: string;
}

interface Personal {
    id: number;
    nombre: string;
}

interface Combustible {
    id: number;
    nombre: string;
}

interface ControlCombustible {
    id: number;
    bien_id: number;
    personal_id: number;
    combustible_id: number;
    numero_formateado: string;
    fecha: string;
    km_recorrido: number;
    cantidad_galones: number;
    precio_galon:  number;
    subtotal: number;
    porcentaje_iva: number;
    valor_iva: number;
    valor_total: number;
}

interface Props {
    bienes: Bien[];
    personales: Personal[];
    combustibles: Combustible[];
    controlCombustible: ControlCombustible,
    porcentaje_iva: number;
    filters: {
        search: string;
        year: number;
    };
}

export default function Edit({ bienes, personales, combustibles, controlCombustible, porcentaje_iva, filters }: Props) {

    const bienesOptions = bienes.map((bien) => {
        let nombre = "";
    
        if (bien.tipo_bien === "G") {
            nombre = bien.identificacion;
        }
    
        if (bien.tipo_bien === "V") {
            nombre = `${bien.placa} - ${bien.clase_vehiculo} ${bien.tipo_vehiculo} ${bien.identificacion}`;
        }
    
        return {
            id: bien.id,
            nombre: nombre,
        };
    });

    const { data, setData, put, processing, errors } = useForm({
        bien_id: controlCombustible.bien_id,
        personal_id: controlCombustible.personal_id,
        combustible_id: controlCombustible.combustible_id,
        numero_formateado: controlCombustible.numero_formateado,
        fecha: controlCombustible.fecha,
        km_recorrido: controlCombustible.km_recorrido,
        cantidad_galones: controlCombustible.cantidad_galones,        
        precio_galon: controlCombustible.precio_galon,
        subtotal: controlCombustible.subtotal,
        porcentaje_iva: porcentaje_iva,
        valor_iva: controlCombustible.valor_iva,
        valor_total: controlCombustible.valor_total,
        search: filters?.search ?? '',
        year: filters.year ?? new Date().getFullYear(),
    });

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/controlescombustibles/${controlCombustible.id}`);
    }

    const [cantidadInput, setCantidadInput] = useState(data.cantidad_galones?.toString() || "");

    const [precioInput, setPrecioInput] = useState(data.precio_galon?.toString() || "");

    useEffect(() => {
        const subtotal = Number(data.cantidad_galones || 0) * Number(data.precio_galon || 0);

        const subtotalRounded = Number(subtotal.toFixed(3));

        const valorIva = subtotalRounded * (porcentaje_iva / 100);

        const total = subtotalRounded + valorIva;

        setData(prev => ({
            ...prev,
            subtotal: subtotalRounded,
            valor_iva: Number(valorIva.toFixed(2)),
            valor_total: Number(total.toFixed(2)),
        }));

    }, [data.cantidad_galones, data.precio_galon, porcentaje_iva]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar control de combustible" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                    <div className="absolute top-3 right-4 bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                        <span className="text-xs opacity-80 mr-1">Control de combustible</span>
                        <span className="font-bold tracking-wider">#{data.numero_formateado}</span>
                    </div>
                    <div className="flex items-start gap-3">
                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                            <Calendar1Icon className="w-8 h-8 text-gray-700" />
                        </div>
                        <div>
                            <h1 className="text-lg font-semibold leading-tight">Fecha</h1>
                            <p className="text-sm text-muted-foreground">
                                {new Date().toLocaleString('es-EC', {
                                    dateStyle: 'long',
                                    timeStyle: 'short'
                                }).toUpperCase()}
                            </p>
                        </div>
                    </div>
                </div>
                <form onSubmit={handleUpdate} className='space-y-4'>
                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                        <div className="grid w-full gap-3 md:col-span-2">
                            <SelectField
                                label="Vehículo o equipo"
                                name="bien_id"
                                value={data.bien_id}
                                options={bienesOptions}
                                error={errors.bien_id}
                                onChange={(value) => setData("bien_id", Number(value))}
                            />
                        </div>                      
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">  
                        <div className="w-full">                            
                            <DateField
                                label="Fecha"
                                value={data.fecha}
                                onChange={(value) => setData("fecha", value)}
                                error={errors.fecha}
                            />                            
                        </div>                         
                        <div className="w-full">
                            <SelectField
                                label="Tipo de combustible"
                                name="combustible_id"
                                value={data.combustible_id}
                                options={combustibles}
                                error={errors.combustible_id}
                                uppercase={true}
                                onChange={(value) => setData("combustible_id", Number(value))}
                            />
                        </div>                     
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">  
                        <div className="w-full">
                            <SelectField
                                label="Chofer asignado"
                                name="personal_id"
                                value={data.personal_id}
                                options={personales}
                                error={errors.personal_id}
                                uppercase={true}
                                onChange={(value) => setData("personal_id", Number(value))}
                            />
                        </div>
                        <div className="w-full">
                            <NumberInput
                                label='Kilometraje de recorrido'                   
                                name="km_recorrido"
                                value={data.km_recorrido}
                                onChange={(value) => setData('km_recorrido', value)}
                                min={0}
                                error={errors.km_recorrido}
                            />
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">  
                        <div className="w-full">
                            <InputField
                                label="Cantidad galones"
                                name="cantidad_galones"
                                value={cantidadInput}
                                onChange={(e) => {
                                    const raw = e.target.value;

                                    if (/^\d*([.,]\d{0,3})?$/.test(raw)) {
                                        setCantidadInput(raw);

                                        const numeric = parseFloat(raw.replace(",", "."));

                                        setData(
                                            "cantidad_galones",
                                            isNaN(numeric) ? 0 : numeric
                                        );
                                    }
                                }}
                                error={errors.cantidad_galones}
                                placeholder="Especifique"
                            />
                        </div>
                        <div className="w-full">
                            <InputField
                                label="Precio x galones"
                                name="cantidad_galones"
                                value={precioInput}
                                onChange={(e) => {
                                    const raw = e.target.value;

                                    if (/^\d*([.,]\d{0,3})?$/.test(raw)) {
                                        setPrecioInput(raw);

                                        const numeric = parseFloat(raw.replace(",", "."));

                                        setData(
                                            "precio_galon",
                                            isNaN(numeric) ? 0 : numeric
                                        );
                                    }
                                }}
                                error={errors.precio_galon}
                                placeholder="Especifique"
                            />
                        </div>
                    </div>
                    <Card className="rounded-xl shadow-sm mb-4">
                        <CardContent className="space-y-3">
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">
                                    Subtotal
                                </span>
                                <div className="flex items-center gap-1">
                                    <span className="font-semibold text-blue-700 dark:text-blue-400 tabular-nums">
                                        {money(data.subtotal, 3)}
                                    </span>
                                    <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                        USD
                                    </span>
                                </div>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">
                                    IVA ({porcentaje_iva}%)
                                </span>
                                <div className="flex items-center gap-1">
                                    <span className="font-semibold text-orange-700 dark:text-orange-400 tabular-nums">
                                        {money(data.valor_iva)}
                                    </span>
                                    <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                        USD
                                    </span>
                                </div>
                            </div>
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
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar
                        </Button>             
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href="/controlescombustibles" 
                                data={{ search: data.search, year: data.year }}
                            >
                                <Undo2Icon /> Regresar
                            </Link>
                        </Button>
                    </div>  
                </form>
            </div>
        </AppLayout>
    )
}