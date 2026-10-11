import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { AlertTriangle, Calendar1Icon, CheckCheckIcon, LockIcon, PlusIcon, RefreshCcwIcon, Trash2, TriangleAlertIcon, Undo2Icon, User2Icon } from 'lucide-react';
import { Alert, AlertTitle } from '@/components/ui/alert';
import React, { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import SearchableSelectField from '@/components/form/SearchableSelectField';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import TextAreaField from '@/components/form/TextAreaField';
import ConfirmDialog from '@/components/form/ConfirmDialog';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar parte diario de consignas',
        href: 'partesconsignas/edit',
    },
];

interface TurnoGuardia {
    id: number;
    nombre: string;
    nombre_oficial: string;
}

interface Personal {
    id: number;
    nombre: string;
}

interface PersonalPuesto {
    id: number;
    nombre: string;
}

interface Funcion {
    funcion_consigna_id: number;
    personal_id: number | null;
    nombre: string;
}

interface Vehiculo {
    bien_id: number;
    tipo_vehiculo: string;
    marca: string;
    identificacion: string;
    operativo: boolean;
    motivo: string;
}

interface ReporteQap {
    personal_id: number;
    hora: string;
    reporte: string;
}

interface Consigna {
    personal_id: number | null;
    consigna: string;
    cumple: boolean;
    motivo: string;
}

interface Planificacion {
    personal_id: number;
    actividad: string;
    cumple: boolean;
    motivo: string;
}

interface ParteConsigna {
    id: number,
    turno_guardia_id: number;
    turno_guardia_recibe_id: number;
    personal_entrega_id: number;
    personal_puesto_revisa_id: number;
    nombre_entrega: string;
    fecha: string;
    novedades: string;
    turno_guardia: {
        nombre: string;
    }   
}

interface Props {
    turnosGuardias: TurnoGuardia[],
    personales: Personal[];
    personalesPuestos: PersonalPuesto[];
    funciones: Funcion[];
    vehiculos: Vehiculo[];
    reportesQap: ReporteQap[];
    consignas: Consigna[];
    planificaciones: Planificacion[];
    parteConsigna: ParteConsigna;
    fecha_siguiente_turno: string;
    filters: {
        search: string;
        year: number;
    };
}

export default function Edit({turnosGuardias, personales, personalesPuestos, funciones, vehiculos, reportesQap, consignas, planificaciones, parteConsigna, fecha_siguiente_turno, filters}: Props) {

    const {data, setData, put, processing, errors} = useForm({
        turno_guardia_id: parteConsigna.turno_guardia_id,
        turno_guardia_recibe_id: parteConsigna.turno_guardia_recibe_id,
        personal_entrega_id: parteConsigna.personal_entrega_id,
        personal_puesto_revisa_id: parteConsigna.personal_puesto_revisa_id,
        nombre_entrega: parteConsigna.nombre_entrega,
        fecha: parteConsigna.fecha,
        novedades: parteConsigna.novedades,
        integrantes: funciones ?? [],
        vehiculos: vehiculos ?? [],
        reportesQap: reportesQap ?? [],
        consignas: consignas ?? [],
        planificaciones: planificaciones ?? [],   
        continua_guardia: true,
        fecha_siguiente_turno: fecha_siguiente_turno,
        action: 'update',
        search: filters?.search ?? '',
        year: filters.year,
    });

    const getIntegranteError = (index: number) => {
        return errors?.[`integrantes.${index}.personal_id`];
    };

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/partesconsignas/${parteConsigna.id}`);
    }   

    const turnosOptions = [
        {
            label: 'Turnos de guardia',
            options: (turnosGuardias ?? []).map(t => ({
                id: t.id,
                nombre: `${t.nombre} - ${t.nombre_oficial}`,
            })),
        },
    ];

    const toggleOperativoVehiculo = (index: number, value: boolean) => {
        const nuevos = [...data.vehiculos];
        nuevos[index].operativo = value;
        if (value) {
            nuevos[index].motivo = "";
        }
        setData("vehiculos", nuevos);
    };

    const changeMotivoVehiculo = (index:number, value:string) => {
        const nuevos = [...data.vehiculos];
        nuevos[index].motivo = value;
        setData("vehiculos", nuevos);
    };

    const [personalQapSeleccionado, setPersonalQapSeleccionado] = useState<number | null>(null);

    const obtenerHoraActual = () => {
        const ahora = new Date()
        return ahora.toLocaleTimeString('es-EC', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        })
    }

    const [horaReporte, setHoraReporte] = useState(obtenerHoraActual());
    const [reporte, setReporte] = useState('');

    const [mensajeReporteQap, setMensajeReporteQap] = useState<{
        texto: string;
        tipo: "success" | "error";
    } | null>(null);

    const handleAgregarReporteQap = () => {
    
        if (!personalQapSeleccionado) {
            setMensajeReporteQap({
                texto: "Debe seleccionar un personal para agregar",
                tipo: "error"
            });
            return;
        }

        if (!horaReporte) {
            setMensajeReporteQap({
                texto: "Debe escribir la hora",
                tipo: "error"
            })
            return
        }

        if (!reporte) {
            setMensajeReporteQap({
                texto: "Debe escribir el reporte",
                tipo: "error"
            })
            return
        }

        if (data.reportesQap.some(p => p.personal_id === personalQapSeleccionado)) {
            setMensajeReporteQap({
                texto: "El personal ya fue agregado",
                tipo: "error"
            });
            return;
        }  
       
        const nuevoRegistro = {
            personal_id: personalQapSeleccionado,
            hora: horaReporte,
            reporte
        }

        setData("reportesQap", [
            ...data.reportesQap,
            nuevoRegistro
        ])

        setMensajeReporteQap({
            texto: "Personal agregado correctamente",
            tipo: "success"
        })

        setPersonalQapSeleccionado(null);
        setHoraReporte(obtenerHoraActual());
        setReporte('');
    }

    useEffect(() => {
        if (!mensajeReporteQap) return;

        const timer = setTimeout(() => {
            setMensajeReporteQap(null);
        }, 3000);

        return () => clearTimeout(timer);
    }, [mensajeReporteQap]);

    const cambiarPersonalConsigna = (index: number, value: number | null) => {
        const nuevos = [...data.consignas]
        nuevos[index].personal_id = value
        setData("consignas", nuevos)
    }

    const toggleCumpleConsigna = (index: number, value: boolean) => {
        const nuevos = [...data.consignas];
        nuevos[index].cumple = value;
        if (value) {
            nuevos[index].motivo = "";
        }
        setData("consignas", nuevos);
    };

    const changeMotivoConsigna = (index:number, value:string) => {
        const nuevos = [...data.consignas];
        nuevos[index].motivo = value;
        setData("consignas", nuevos);
    };

    const [personalConsignaSeleccionado, setPersonalConsignaSeleccionado] = useState<number | null>(null);
    const [consignaTexto, setConsignaTexto] = useState('');
    
    const [mensajeConsigna, setMensajeConsigna] = useState<{
        texto: string;
        tipo: "success" | "error";
    } | null>(null);

    const handleAgregarConsigna = () => {

       if (!personalConsignaSeleccionado) {
            setMensajeConsigna({
                texto: "Debe seleccionar un personal para agregar",
                tipo: "error"
            });
            return;
        }       

        if (!consignaTexto) {
            setMensajeConsigna({
                texto: "Debe escribir la consigna",
                tipo: "error"
            })
            return
        }

        if (data.consignas.some(p => p.personal_id === personalConsignaSeleccionado)) {
            setMensajeConsigna({
                texto: "El personal ya fue agregado",
                tipo: "error"
            });
            return;
        }  

        const nuevaConsigna = {
            personal_id: personalConsignaSeleccionado,
            consigna: consignaTexto,
            cumple: false,
            motivo: ''
        };

        setData("consignas", [
            ...data.consignas,
            nuevaConsigna
        ]);

        setPersonalConsignaSeleccionado(null);
        setConsignaTexto("");
    };

    useEffect(() => {
        if (!mensajeConsigna) return;

        const timer = setTimeout(() => {
            setMensajeConsigna(null);
        }, 3000);

        return () => clearTimeout(timer);
    }, [mensajeConsigna]);

    const toggleCumplePlanificacion = (index: number, value: boolean) => {
        const nuevos = [...data.planificaciones];
        nuevos[index].cumple = value;
        if (value) {
            nuevos[index].motivo = "";
        }
        setData("planificaciones", nuevos);
    };

    const changeMotivoPlanificacion = (index:number, value:string) => {
        const nuevos = [...data.planificaciones];
        nuevos[index].motivo = value;
        setData("planificaciones", nuevos);
    };

    const [personalPlanificacionSeleccionado, setPersonalPlanificacionSeleccionado] = useState<number | null>(null);
    const [actividadTexto, setActividadTexto] = useState('');
    
    const [mensajePlanificacion, setMensajePlanificacion] = useState<{
        texto: string;
        tipo: "success" | "error";
    } | null>(null);

    const handleAgregarPlanificacion = () => {

       if (!personalPlanificacionSeleccionado) {
            setMensajePlanificacion({
                texto: "Debe seleccionar un personal para agregar",
                tipo: "error"
            });
            return;
        }       

        if (!actividadTexto) {
            setMensajePlanificacion({
                texto: "Debe escribir la actividad",
                tipo: "error"
            })
            return
        }

        if (data.planificaciones.some(p => p.personal_id === personalPlanificacionSeleccionado)) {
            setMensajePlanificacion({
                texto: "El personal ya fue agregado",
                tipo: "error"
            });
            return;
        }  

        const nuevaActividad = {
            personal_id: personalPlanificacionSeleccionado,
            actividad: actividadTexto,
            cumple: false,
            motivo: ''
        };

        setData("planificaciones", [
            ...data.planificaciones,
            nuevaActividad
        ]);

        setPersonalPlanificacionSeleccionado(null);
        setActividadTexto("");
    };

    useEffect(() => {
        if (!mensajePlanificacion) return;

        const timer = setTimeout(() => {
            setMensajePlanificacion(null);
        }, 3000);

        return () => clearTimeout(timer);
    }, [mensajePlanificacion]);

    const [openCloseDialog, setOpenCloseDialog] = useState<number | null>(null);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar parte diario de consignas" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="border rounded-lg bg-background shadow-sm p-5 text-[13px] space-y-4"> 
                    <div className="flex items-center justify-between border-b pb-3">        
                        <div className="flex items-center gap-3">
                            <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                <Calendar1Icon className="w-8 h-8 text-gray-700" />
                            </div>
                            <div>
                                <h1 className="text-sm font-medium text-muted-foreground">Fecha</h1>
                                <p className="text-base font-semibold tracking-wide">
                                    {new Date(parteConsigna.fecha + 'T00:00:00')
                                        .toLocaleDateString('es-EC', { dateStyle: 'long' })
                                        .toUpperCase()}
                                </p>
                            </div>
                        </div>
                        <div className="bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                            <span className="text-xs opacity-80 mr-1">Turno de guardia</span>
                            <span className="font-bold tracking-wider">#{parteConsigna.turno_guardia.nombre}</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                            <User2Icon className="w-8 h-8 text-gray-700" />
                        </div>
                        <div>                        
                            <h1 className="text-sm font-medium text-muted-foreground">Oficial de guardia</h1>
                            <p className="text-base font-semibold tracking-wide">{parteConsigna.nombre_entrega}</p>
                        </div>
                    </div>
                </div>                
                <form onSubmit={handleUpdate} className='space-y-4'>                                    
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Nómina operativa
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {funciones.length ? (
                                <div className="border rounded-xl shadow-sm overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="bg-muted">
                                                    <th className="w-10 py-2 px-4 text-left border-b">#</th>
                                                    <th className="w-50 py-2 px-4 text-left border-b">Función</th>
                                                    <th className="w-120 py-2 px-4 text-left border-b">Personal asignado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.integrantes.map((funcion, index) => (                                                                                          
                                                    <tr className="odd:bg-muted/50 hover:bg-gray-50" key={funcion.funcion_consigna_id}>
                                                        <td className="py-2 px-4 border-b">{index + 1}</td>
                                                        <td className="py-2 px-4 border-b">{funcion.nombre}</td>
                                                        <td className="py-2 px-4 border-b">                                                       
                                                            <SearchableSelectField
                                                                name={`integrantes.${index}.personal_id`}
                                                                value={funcion.personal_id}
                                                                options={personales ?? []}
                                                                onChange={(value) => {

                                                                    const nuevos = [...data.integrantes]

                                                                    nuevos[index].personal_id =
                                                                        typeof value === "number" ? value : 0

                                                                    setData('integrantes', nuevos)
                                                                }}
                                                                error={getIntegranteError(index)} 
                                                            />
                                                        </td>
                                                    </tr>                                               
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ) : (
                                <div className="flex justify-center mt-6">
                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                        <TriangleAlertIcon className="h-4 w-4" />
                                        <AlertTitle>Este parte diario no cuenta con integrantes</AlertTitle>                                        
                                    </Alert>
                                </div>
                            )}       
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Vehículos operativos
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {vehiculos.length ? (
                                <div className="border rounded-xl shadow-sm overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="bg-muted">
                                                    <th className="w-10 py-2 px-4 text-left border-b">#</th>
                                                    <th className="w-100 py-2 px-4 text-left border-b">Vehículo</th>
                                                    <th className="w-20 py-2 px-4 text-left border-b">Operativo</th>
                                                    <th className="w-100 py-2 px-4 text-left border-b">Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.vehiculos.map((vehiculo, index) => (                                                                                          
                                                    <tr className="odd:bg-muted/50 hover:bg-gray-50" key={vehiculo.bien_id}>
                                                        <td className="py-2 px-4 border-b">{index + 1}</td>
                                                        <td className="py-2 px-4 border-b">{vehiculo.tipo_vehiculo} - {vehiculo.marca} - {vehiculo.identificacion}</td>
                                                        <td className="py-2 px-4 border-b">                                                       
                                                            <Switch
                                                                checked={vehiculo.operativo}
                                                                onCheckedChange={(value) => toggleOperativoVehiculo(index, value)}
                                                            />
                                                        </td>
                                                        <td className="py-2 px-4 border-b">                                                       
                                                            <Textarea
                                                                disabled={vehiculo.operativo}
                                                                value={vehiculo.motivo ?? ''}
                                                                onChange={(e) => changeMotivoVehiculo(index, e.target.value)}
                                                                className="min-h-[60px] resize-none"
                                                            />
                                                        </td>
                                                    </tr>                                               
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ) : (
                                <div className="flex justify-center mt-6">
                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                        <TriangleAlertIcon className="h-4 w-4" />
                                        <AlertTitle>Este parte diario no cuenta con vehículos</AlertTitle>                                        
                                    </Alert>
                                </div>
                            )} 
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Reporte del personal en QAP
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="w-full space-y-3">
                                <div className="grid grid-cols-12 gap-3 items-end">
                                    <div className="col-span-7 flex items-center gap-2">
                                        <Label className="whitespace-nowrap">Personal</Label>
                                        <SearchableSelectField
                                            name="personalQap"
                                            value={personalQapSeleccionado ?? null}
                                            options={personales}
                                            onChange={(value) => setPersonalQapSeleccionado(value ? Number(value) : null)}
                                        />
                                    </div>
                                    <div className="col-span-3 flex items-center gap-2">
                                        <Label className="whitespace-nowrap">Hora</Label>
                                        <Input
                                            type="time"
                                            value={horaReporte}
                                            onChange={(e) => setHoraReporte(e.target.value)}
                                            className="w-full"
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Button
                                            type="button"
                                            className="w-full bg-slate-500 hover:bg-slate-700 cursor-pointer"
                                            onClick={handleAgregarReporteQap}
                                        >
                                            <PlusIcon className="h-4 w-4 mr-1" />
                                            Agregar
                                        </Button>
                                    </div>
                                </div>
                                <div className="grid grid-cols-12 gap-3 items-center">
                                    <div className="col-span-12 flex items-center gap-3">
                                        <Label className="whitespace-nowrap">Reporte</Label>
                                        <Input
                                            className="col-span-11"
                                            type="text"
                                            value={reporte}
                                            onChange={(e) => setReporte(e.target.value)}
                                            placeholder="Especifique"
                                        />
                                    </div>
                                </div>
                                {mensajeReporteQap && (
                                    <Alert                                        
                                        className={`mt-4 border-none w-full px-4 py-2
                                        ${
                                            mensajeReporteQap.tipo === "error"
                                            ? "bg-red-500/10 text-red-600"
                                            : "bg-green-500/10 text-green-600"
                                        }
                                        `}
                                    >
                                        {mensajeReporteQap.tipo === "error" ? (
                                            <TriangleAlertIcon className="h-4 w-4" />
                                        ) : (
                                            <CheckCheckIcon className="h-4 w-4" />
                                        )}
                                        <AlertTitle>{mensajeReporteQap.texto}</AlertTitle>
                                    </Alert>
                                )}
                            </div>
                            {data.reportesQap.length ? (
                                <div className="border rounded-xl shadow-sm overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="bg-muted">
                                                    <th className="w-10 py-2 px-4 text-left border-b">#</th>
                                                    <th className="w-100 py-2 px-4 text-left border-b">Personal</th>
                                                    <th className="w-20 py-2 px-4 text-left border-b">Hora</th>
                                                    <th className="w-100 py-2 px-4 text-left border-b">Reporte</th>
                                                    <th className="w-10 py-2 px-4 text-left border-b"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.reportesQap.map((reporte, index) => {                                                                                          
                                                    const personalObj = personales?.find(p => p.id === reporte.personal_id);
                                                    
                                                    return (
                                                        <tr className="odd:bg-muted/50 hover:bg-gray-50" key={index}>
                                                            <td className="py-2 px-4 border-b">{index + 1}</td>
                                                            <td className="py-2 px-4 border-b">{personalObj?.nombre ?? "No encontrado"}</td>
                                                            <td className="py-2 px-4 border-b">{reporte.hora.substring(0,5)}</td>
                                                            <td className="py-2 px-4 border-b">{reporte.reporte}</td>
                                                            <td className="py-2 px-4 border-b">
                                                                <Button
                                                                    type="button"
                                                                    size="sm"
                                                                    variant="destructive"
                                                                    className="cursor-pointer"
                                                                    onClick={() =>
                                                                        setData(
                                                                            "reportesQap",
                                                                            data.reportesQap.filter(
                                                                                p => p.personal_id !== reporte.personal_id
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
                                </div>
                            ) : (
                                <div className="flex justify-center mt-6">
                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                        <TriangleAlertIcon className="h-4 w-4" />
                                        <AlertTitle>Este parte diario no cuenta con reportes del personal en QAP</AlertTitle>                                        
                                    </Alert>
                                </div>
                            )} 
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Consignas / actividades
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="w-full space-y-3">
                                <div className="grid grid-cols-12 gap-3 items-end w-full">    
                                    <div className="col-span-10 flex items-center gap-2 w-full">
                                        <Label className="whitespace-nowrap">Personal</Label>
                                        <div className="flex-1">
                                            <SearchableSelectField
                                                name="personalQap"
                                                value={personalConsignaSeleccionado ?? null}
                                                options={personales}
                                                onChange={(value) => setPersonalConsignaSeleccionado(value ? Number(value) : null)}
                                            />
                                        </div>   
                                    </div>                                 
                                    <div className="col-span-2">
                                        <Button
                                            type="button"
                                            className="w-full bg-slate-500 hover:bg-slate-700 cursor-pointer"
                                            onClick={handleAgregarConsigna}
                                        >
                                            <PlusIcon className="h-4 w-4 mr-1" />
                                            Agregar
                                        </Button>
                                    </div>
                                </div>   
                                <div className="grid grid-cols-12 gap-3 items-center">
                                    <div className="col-span-12 flex items-center gap-3">
                                        <Label className="whitespace-nowrap">Consigna</Label>
                                        <Input
                                            className="col-span-11"
                                            type="text"
                                            value={consignaTexto}
                                            onChange={(e) => setConsignaTexto(e.target.value)}                                            
                                            placeholder='Especifique'
                                        />
                                    </div>
                                </div>                             
                                {mensajeConsigna && (
                                    <Alert                                        
                                        className={`mt-4 border-none w-full px-4 py-2
                                        ${
                                            mensajeConsigna.tipo === "error"
                                            ? "bg-red-500/10 text-red-600"
                                            : "bg-green-500/10 text-green-600"
                                        }
                                        `}
                                    >
                                        {mensajeConsigna.tipo === "error" ? (
                                            <TriangleAlertIcon className="h-4 w-4" />
                                        ) : (
                                            <CheckCheckIcon className="h-4 w-4" />
                                        )}
                                        <AlertTitle>{mensajeConsigna.texto}</AlertTitle>
                                    </Alert>
                                )}
                            </div>
                            {data.consignas.length ? (
                                <div className="border rounded-xl shadow-sm overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="bg-muted">
                                                    <th className="w-10 py-2 px-4 text-left border-b">#</th>
                                                    <th className="w-120 py-2 px-4 text-left border-b">Responsable</th>
                                                    <th className="w-80 py-2 px-4 text-left border-b">Consigna / actividad</th>
                                                    <th className="w-20 py-2 px-4 text-left border-b">Cumple</th>
                                                    <th className="w-80 py-2 px-4 text-left border-b">Motivo</th>
                                                    <th className="w-10 py-2 px-4 text-left border-b"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.consignas.map((consigna, index) => (
                                                    <tr className="odd:bg-muted/50 hover:bg-gray-50" key={index}>
                                                        <td className="py-2 px-4 border-b">{index + 1}</td>
                                                        <td className="py-2 px-4 border-b">
                                                            <SearchableSelectField
                                                                name={`consignas.${index}.personal_id`}
                                                                value={consigna.personal_id}
                                                                options={personales ?? []}
                                                                onChange={(value) => cambiarPersonalConsigna(index, value ? Number(value) : null)}
                                                            />                                                            
                                                        </td>
                                                        <td className="py-2 px-4 border-b">{consigna.consigna}</td>
                                                        <td className="py-2 px-4 border-b">                                                       
                                                            <Switch
                                                                checked={consigna.cumple}
                                                                onCheckedChange={(value) => toggleCumpleConsigna(index, value)}
                                                            />
                                                        </td>
                                                        <td className="py-2 px-4 border-b">  
                                                            <Textarea
                                                                disabled={consigna.cumple}
                                                                value={consigna.motivo ?? ''}
                                                                onChange={(e) => changeMotivoConsigna(index, e.target.value)}
                                                                className="min-h-[60px] resize-none"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-4 border-b">
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="destructive"
                                                                className="cursor-pointer"
                                                                onClick={() =>
                                                                    setData(
                                                                        "consignas",
                                                                        data.consignas.filter(
                                                                            p => p.personal_id !== consigna.personal_id
                                                                        )
                                                                    )
                                                                }
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </td>
                                                    </tr>                               
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>  
                            ) : (
                                <div className="flex justify-center mt-6">
                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                        <TriangleAlertIcon className="h-4 w-4" />
                                        <AlertTitle>Este parte diario no cuenta con consignas</AlertTitle>                                        
                                    </Alert>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Planificación diaria
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="w-full space-y-3">
                                <div className="grid grid-cols-12 gap-3 items-end w-full">    
                                    <div className="col-span-10 flex items-center gap-2 w-full">
                                        <Label className="whitespace-nowrap">Personal</Label>
                                        <div className="flex-1">
                                            <SearchableSelectField
                                                name="personalPlanificacion"
                                                value={personalPlanificacionSeleccionado ?? null}
                                                options={personales}
                                                onChange={(value) => setPersonalPlanificacionSeleccionado(value ? Number(value) : null)}
                                            />
                                        </div>   
                                    </div>                                 
                                    <div className="col-span-2">
                                        <Button
                                            type="button"
                                            className="w-full bg-slate-500 hover:bg-slate-700 cursor-pointer"
                                            onClick={handleAgregarPlanificacion}
                                        >
                                            <PlusIcon className="h-4 w-4 mr-1" />
                                            Agregar
                                        </Button>
                                    </div>
                                </div>   
                                <div className="grid grid-cols-12 gap-3 items-center">
                                    <div className="col-span-12 flex items-center gap-3">
                                        <Label className="whitespace-nowrap">Actividad</Label>
                                        <Input
                                            className="col-span-11"
                                            type="text"
                                            value={actividadTexto}
                                            onChange={(e) => setActividadTexto(e.target.value)}                                            
                                            placeholder='Especifique'
                                        />
                                    </div>
                                </div>                             
                                {mensajePlanificacion && (
                                    <Alert                                        
                                        className={`mt-4 border-none w-full px-4 py-2
                                        ${
                                            mensajePlanificacion.tipo === "error"
                                            ? "bg-red-500/10 text-red-600"
                                            : "bg-green-500/10 text-green-600"
                                        }
                                        `}
                                    >
                                        {mensajePlanificacion.tipo === "error" ? (
                                            <TriangleAlertIcon className="h-4 w-4" />
                                        ) : (
                                            <CheckCheckIcon className="h-4 w-4" />
                                        )}
                                        <AlertTitle>{mensajePlanificacion.texto}</AlertTitle>
                                    </Alert>
                                )}
                            </div>
                            {data.planificaciones.length ? (
                                <div className="border rounded-xl shadow-sm overflow-hidden">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="bg-muted">
                                                    <th className="w-10 py-2 px-4 text-left border-b">#</th>
                                                    <th className="w-120 py-2 px-4 text-left border-b">Responsable</th>
                                                    <th className="w-80 py-2 px-4 text-left border-b">Actividad</th>
                                                    <th className="w-20 py-2 px-4 text-left border-b">Cumple</th>
                                                    <th className="w-80 py-2 px-4 text-left border-b">Motivo</th>
                                                    <th className="w-10 py-2 px-4 text-left border-b"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.planificaciones.map((planificacion, index) => {
                                                    const personalObj = personales?.find(p => p.id === planificacion.personal_id);
                                                    
                                                    return (
                                                        <tr className="odd:bg-muted/50 hover:bg-gray-50" key={index}>
                                                            <td className="py-2 px-4 border-b">{index + 1}</td>
                                                            <td className="py-2 px-4 border-b">{personalObj?.nombre ?? "No encontrado"}</td>
                                                            <td className="py-2 px-4 border-b">{planificacion.actividad}</td>
                                                            <td className="py-2 px-4 border-b">                                                       
                                                                <Switch
                                                                    checked={planificacion.cumple}
                                                                    onCheckedChange={(value) => toggleCumplePlanificacion(index, value)}
                                                                />
                                                            </td>
                                                            <td className="py-2 px-4 border-b">  
                                                                <Textarea
                                                                    disabled={planificacion.cumple}
                                                                    value={planificacion.motivo ?? ''}
                                                                    onChange={(e) => changeMotivoPlanificacion(index, e.target.value)}
                                                                    className="min-h-[60px] resize-none"
                                                                />
                                                            </td>
                                                            <td className="py-2 px-4 border-b">
                                                                <Button
                                                                    type="button"
                                                                    size="sm"
                                                                    variant="destructive"
                                                                    className="cursor-pointer"
                                                                    onClick={() =>
                                                                        setData(
                                                                            "planificaciones",
                                                                            data.planificaciones.filter(
                                                                                p => p.personal_id !== planificacion.personal_id
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
                                </div>  
                            ) : (
                                <div className="flex justify-center mt-6">
                                    <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                        <TriangleAlertIcon className="h-4 w-4" />
                                        <AlertTitle>Este parte diario no cuenta con planificaciones diarias</AlertTitle>                                        
                                    </Alert>
                                </div>
                            )} 
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold leading-none">
                                Novedades
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="w-full space-y-3">
                                <TextAreaField
                                    name="novedades"
                                    value={data.novedades ?? ''}
                                    maxLength={10000}
                                    placeholder="Especifique"
                                    error={errors.novedades}
                                    onChange={value => setData('novedades', value)}
                                />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="rounded-xl shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-semibold leading-none">
                                Entrega y recepción del turno de guardia
                            </CardTitle>
                            <div className="flex items-center gap-3 bg-muted/50 rounded-lg">                                
                                <Switch
                                    id="continua_guardia"
                                    checked={data.continua_guardia}
                                    onCheckedChange={(checked) => {
                                        setData('continua_guardia', checked);

                                        if (checked) {
                                            // continúa guardia → limpiar selección
                                            setData('turno_guardia_recibe_id', 0);
                                        }
                                    }}
                                />
                                <Label htmlFor="continua_guardia">Continua con el turno de guardia</Label>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="w-full space-y-3">
                                <div className="flex items-center justify-between rounded-lg border p-3">    
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm text-muted-foreground whitespace-nowrap">
                                            {data.continua_guardia
                                                ? 'Continúa la guardia el día'
                                                : 'Entrega de guardia para el día'}
                                        </span>   
                                        <span className="text-sm font-semibold text-primary whitespace-nowrap">
                                            {new Date(data.fecha_siguiente_turno + 'T00:00:00')
                                                .toLocaleDateString('es-EC', { dateStyle: 'long' })
                                                .toUpperCase()}
                                        </span>
                                    </div>
                                    <span className={`text-xs px-2 py-1 rounded-full ${
                                        data.continua_guardia
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-blue-100 text-blue-700'
                                    }`}>
                                        {data.continua_guardia ? 'CONTINÚA' : 'ENTREGA'}
                                    </span>
                                </div>
                                <div className='grid w-full gap-3 items-start'>
                                    <SearchableSelectField
                                        label="Turno de guardía que recibe las consignas"
                                        name="turno_guardia_recibe_id"
                                        value={data.turno_guardia_recibe_id}
                                        options={turnosOptions ?? []}
                                        disabled={data.continua_guardia}
                                        error={errors.turno_guardia_recibe_id}
                                        onChange={(value) => setData("turno_guardia_recibe_id", Number(value))}
                                    />
                                </div>
                                <div className='grid w-full gap-3 items-start'>
                                    <SearchableSelectField
                                        label="Oficial que revisa el parte"
                                        name="personal_puesto_revisa_id"
                                        value={data.personal_puesto_revisa_id}
                                        options={personalesPuestos ?? []}                                        
                                        error={errors.personal_puesto_revisa_id}
                                        onChange={(value) => setData("personal_puesto_revisa_id", Number(value))}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    {Object.keys(errors || {}).length > 0 && (                        
                        <div className="flex justify-center w-full items-start gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">    <AlertTriangle className="h-4 w-4 mt-0.5" />
                            <div>
                                <p className="font-medium">Hay errores en el formulario</p>
                                <p className="text-xs text-red-600">
                                    Revisa los campos marcados en rojo antes de continuar.
                                </p>
                            </div>
                        </div>                       
                    )}
                    <div className="flex gap-2">                           
                        <Button
                            disabled={processing}
                            type="submit"
                            size="sm"
                            className="cursor-pointer"
                            onClick={() => setData('action', 'update')}
                        >
                            {!processing && <RefreshCcwIcon />}
                            {processing && <Spinner />}
                            Actualizar parte
                        </Button>     
                        <Button
                            disabled={processing}
                            type="button"
                            size="sm"
                            className="bg-red-600 hover:bg-red-700 cursor-pointer"
                            onClick={() => setOpenCloseDialog(parteConsigna.id)}
                        >
                            <LockIcon />
                            Cerrar turno
                        </Button>             
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href='/partesconsignas' 
                                data={{ search: data.search, year: data.year }}
                            >
                                <Undo2Icon /> Regresar
                            </Link>
                        </Button>
                    </div> 
                </form>          
            </div>
            <ConfirmDialog
                open={openCloseDialog !== null}
                onOpenChange={() => setOpenCloseDialog(null)}
                title="¿Cerrar turno de guardia?"
                description="Una vez cerrado, no podrá ser modificado."
                confirmText="Cerrar turno"
                cancelText="Cancelar"
                variant="close"
                onConfirm={() => {
                    router.post(`/partesconsignas/${openCloseDialog}/close`, {
                        continua_guardia: data.continua_guardia,
                        fecha_siguiente_turno: data.fecha_siguiente_turno,
                        turno_guardia_recibe_id: data.turno_guardia_recibe_id,
                        search: data.search,
                        year: data.year,
                    }, {
                        preserveScroll: true,
                    });

                    setOpenCloseDialog(null);
                }}
            />
        </AppLayout>
    )
}
