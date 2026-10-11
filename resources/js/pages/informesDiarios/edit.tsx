import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { BarChart3, Bell, Car, CheckCircle2, RefreshCcw, RefreshCcwIcon, SaveIcon, Undo2Icon, XCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import DateField from '@/components/form/DateField';
import { useEffect } from 'react';
import axios from 'axios';
import SearchableSelectField from '@/components/form/SearchableSelectField';
import RichTextEditor from '@/components/form/RichTextEditor';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edtiar informe diario',
        href: '/informesdiarios/edit',
    },
];

interface PersonalPuesto {
    id: number;
    nombre: string;
}

interface InformeDiario {
    id: number;
    personal_puesto_dirige_id: number;
    numero: string;
    fecha_informe: string;
    contenido: string;
    inspecciones_aprobadas: number;
    inspecciones_reinspeccion: number;
    inspecciones_no_realizadas: number;
    inspecciones_vehiculares: number;
    notificaciones_realizadas: number;
    total_general: number; 
    observaciones: string;
    despedida: string;
}

interface Props {
    personalesPuestos: PersonalPuesto[];
    informeDiario: InformeDiario;
    numero: string;
    filters: {
        year: number | null;
        search: string;
    };
}

export default function Edit({personalesPuestos, informeDiario, filters}: Props) {

    const { data, setData, put, processing, errors } = useForm({    
        personal_puesto_dirige_id: informeDiario.personal_puesto_dirige_id,
        numero: informeDiario.numero,
        fecha_informe: informeDiario.fecha_informe,
        contenido: informeDiario.contenido,
        inspecciones_aprobadas: informeDiario.inspecciones_aprobadas,
        inspecciones_reinspeccion: informeDiario.inspecciones_reinspeccion,
        inspecciones_no_realizadas: informeDiario.inspecciones_no_realizadas,
        inspecciones_vehiculares: informeDiario.inspecciones_vehiculares,
        notificaciones_realizadas: informeDiario.notificaciones_realizadas,
        total_general: informeDiario.total_general,
        observaciones: informeDiario.observaciones,
        despedida: informeDiario.despedida,
        search: filters?.search ?? '',
        year: filters.year ?? new Date().getFullYear(),
    });

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/informesdiarios/${informeDiario.id}`);
    }  

    useEffect(() => {
        if (!data.fecha_informe) return;

       axios.get("/informesdiarios/obtenerTotales", {
            params: {
                fecha: data.fecha_informe,
            },
        })
        .then((response) => {
            setData((prev) => ({
                ...prev,
                ...response.data,
            }));
        })
        .catch((error) => {
            console.error(error);
        });
    }, [data.fecha_informe]);
    
    const stats = [
        {
            title: "Inspecciones aprobadas",
            value: data.inspecciones_aprobadas,
            icon: CheckCircle2,
            bg: "bg-green-100 dark:bg-green-900/30",
            color: "text-green-600",
        },
        {
            title: "Locales sujetos a reinspección",
            value: data.inspecciones_reinspeccion,
            icon: RefreshCcw,
            bg: "bg-yellow-100 dark:bg-yellow-900/30",
            color: "text-yellow-600",
        },
        {
            title: "Inspecciones sin realizar",
            value: data.inspecciones_no_realizadas,
            icon: XCircle,
            bg: "bg-red-100 dark:bg-red-900/30",
            color: "text-red-600",
        },
        {
            title: "Inspecciones vehiculares",
            value: data.inspecciones_vehiculares,
            icon: Car,
            bg: "bg-blue-100 dark:bg-blue-900/30",
            color: "text-blue-600",
        },
        {
            title: "Notificaciones realizadas",
            value: data.notificaciones_realizadas,
            icon: Bell,
            bg: "bg-purple-100 dark:bg-purple-900/30",
            color: "text-purple-600",
        },
        {
            title: "Total general",
            value: data.total_general,
            icon: BarChart3,
            bg: "bg-cyan-100 dark:bg-cyan-900/30",
            color: "text-cyan-600",
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar informe diario" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">            
                <form onSubmit={handleUpdate} className='space-y-4'>
                    <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px]">
                        <div className="flex justify-end items-start mb-4">                            
                            <div className="bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                                <span className="font-bold tracking-wider">#{data.numero}</span>
                            </div>
                        </div>
                        <Card className="rounded-xl shadow-sm mb-4">
                            <CardContent className="space-y-3">
                                <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>                        
                                    <div className="grid w-full gap-3">
                                        <DateField
                                            label="Fecha del informe"
                                            value={data.fecha_informe}
                                            onChange={(value) => setData("fecha_informe", value)}
                                            error={errors.fecha_informe}
                                            disableFuture
                                        />
                                    </div>
                                </div>
                                <div className='grid w-full gap-3 items-start'>
                                    <SearchableSelectField
                                        label="Dirigido a"
                                        name="personal_puesto_dirige_id"
                                        value={data.personal_puesto_dirige_id}
                                        options={personalesPuestos ?? []}                                        
                                        error={errors.personal_puesto_dirige_id}
                                        onChange={(value) => setData("personal_puesto_dirige_id", Number(value))}
                                    />
                                </div>
                                <div className='grid w-full gap-3 items-start'>
                                    <RichTextEditor
                                        label="Contenido"
                                        value={data.contenido}
                                        onChange={(value) => setData('contenido', value)}
                                        error={errors.contenido}
                                    />
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {stats.map((item, index) => {
                                        const Icon = item.icon;

                                        return (
                                            <div
                                                key={index}
                                                className="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm hover:shadow-md transition"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div className={`p-3 rounded-lg ${item.bg}`}>
                                                        <Icon className={`w-6 h-6 ${item.color}`} />
                                                    </div>

                                                    <div>
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            {item.title}
                                                        </p>
                                                        <p className={`text-2xl font-bold ${item.color}`}>
                                                            {item.value}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>                                        
                                <div className='grid w-full gap-3 items-start'>
                                    <RichTextEditor
                                        label="Observaciones"
                                        value={data.observaciones}
                                        onChange={(value) => setData('observaciones', value)}
                                        error={errors.observaciones}
                                    />
                                </div>
                                <div className='grid w-full gap-3 items-start'>
                                    <RichTextEditor
                                        label="Despedida"
                                        value={data.despedida}
                                        onChange={(value) => setData('despedida', value)}
                                        error={errors.despedida}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar
                        </Button>  
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href={`/informesdiarios`}
                                data={{ search: data.search, year: data.year}}
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