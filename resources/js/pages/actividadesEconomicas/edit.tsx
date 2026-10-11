import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { useEffect, useState } from 'react';
import axios from 'axios';
import { ChevronDownIcon, MapPinIcon, RefreshCcwIcon, Undo2Icon } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';
import SearchableSelectField from '@/components/form/SearchableSelectField';
import InputField from '@/components/form/InputField';
import DateField from "@/components/form/DateField";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MapPicker from '@/components/MapPicker';
import { Label } from '@/components/ui/label';
import NumberInput from '@/components/form/NumberInput';
import { cn } from '@/lib/utils';
import InputError from '@/components/input-error';

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

interface Compania {
    id: number;
    nombre: string;
}

interface ActividadEconomica {
    id: number,
    cliente_id: number;
    provincia_id: number | null;
    canton_id: number | null;
    parroquia_id: number | null;
    tipo_actividad: string;
    e_nombre_comercial: string | null;
    e_actividad_principal: string | null;
    e_actividad_secundaria: string | null;
    e_clave_catastral: string | null;
    e_sector: string | null;
    e_direccion: string | null;
    e_telefonos: string | null;
    e_latitud: number | null;
    e_longitud: number | null;
    v_compania_id: number | null;
    v_placa: string | null;
    v_marca: string | null;        
    v_clase: string | null;
    v_color: string | null;
    v_tipo: string | null;
    v_cilindraje: number | null;
    v_combustible: string | null;        
    v_numero_unidad: string | null;
    fecha_inicio: string;    
}

interface Props {
    provincias: Provincia[];
    companias: Compania[];
    actividadEconomica: ActividadEconomica;
    filters: {
        search: string;
    };
}

export default function Edit({provincias, companias, actividadEconomica, filters}: Props) {  
    
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Editar actividad económica',
            href: `/actividadeseconomicas/edit/${actividadEconomica.id}`,
        },
    ];

    const {data, setData, put, processing, errors} = useForm({
        cliente_id: actividadEconomica.cliente_id,
        provincia_id: actividadEconomica.provincia_id,
        canton_id: actividadEconomica.canton_id,
        parroquia_id: actividadEconomica.parroquia_id,
        tipo_actividad: actividadEconomica.tipo_actividad,
        e_nombre_comercial: actividadEconomica.e_nombre_comercial,
        e_actividad_principal: actividadEconomica.e_actividad_principal,
        e_actividad_secundaria: actividadEconomica.e_actividad_secundaria,
        e_clave_catastral: actividadEconomica.e_clave_catastral,
        e_sector: actividadEconomica.e_sector,
        e_direccion: actividadEconomica.e_direccion,
        e_telefonos: actividadEconomica.e_telefonos,
        e_latitud: actividadEconomica.e_latitud,
        e_longitud: actividadEconomica.e_longitud,
        v_compania_id: actividadEconomica.v_compania_id,
        v_placa: actividadEconomica.v_placa,
        v_marca: actividadEconomica.v_marca,        
        v_clase: actividadEconomica.v_clase,
        v_color: actividadEconomica.v_color,
        v_tipo: actividadEconomica.v_tipo,
        v_cilindraje: actividadEconomica.v_cilindraje,
        v_combustible: actividadEconomica.v_combustible,
        v_numero_unidad: actividadEconomica.v_numero_unidad,
        fecha_inicio: actividadEconomica.fecha_inicio?.split('T')[0] ?? '',
        search: filters?.search ?? '',
    });

    const handleUpdate = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/actividadeseconomicas/${actividadEconomica.id}`);
    }

    const [cantones, setCantones] = useState<Canton[]>([]);
    const [parroquias, setParroquias] = useState<Parroquia[]>([]);

    useEffect(() => {
        if (!data.provincia_id) return;

        axios.get(`/cantones/obtenerCanton/${data.provincia_id}`)
            .then(res => setCantones(res.data))
            .catch(() => setCantones([]));
    }, [data.provincia_id]);
    
    useEffect(() => {
        if (!data.canton_id) return;
        
        axios.get(`/parroquias/obtenerParroquia/${data.canton_id}`)
            .then(res => setParroquias(res.data))
            .catch(() => setParroquias([]));
    }, [data.canton_id]);

    const getCurrentLocation = () => {
        if (!navigator.geolocation) {
            alert('La geolocalización no está soportada por este navegador.');
            return;
        }
    
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
    
                setData('e_latitud', latitude);
                setData('e_longitud', longitude);
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Actualizar actividad económica" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleUpdate} className='space-y-4'>
                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                        <div className="grid w-full gap-3">
                            <SearchableSelectField
                                label="Provincia"
                                name="provincia_id"
                                value={data.provincia_id}
                                options={provincias}
                                error={errors.provincia_id}
                                onChange={(value) => {
                                    setData("provincia_id", Number(value));
                                    setData("canton_id", null);
                                    setData("parroquia_id", null);
                                }}
                            />
                        </div>
                        <div className="grid w-full gap-3 items-start">
                            <SearchableSelectField
                                label="Cantón"
                                name="canton_id"
                                value={data.canton_id}
                                options={cantones}
                                error={errors.canton_id}
                                onChange={(value) => {
                                    setData("canton_id", Number(value));
                                    setData("parroquia_id", null);
                                }}
                            />
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>                        
                        <div className="grid w-full gap-3">
                            <SearchableSelectField
                                label="Parroquia"
                                name="parroquia_id"
                                value={data.parroquia_id}
                                options={parroquias}
                                error={errors.parroquia_id}
                                onChange={(value) => setData("parroquia_id", Number(value))}
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <DateField
                                label="Fecha de inicio de actividades"
                                value={data.fecha_inicio}
                                onChange={(value) => setData("fecha_inicio", value)}
                                error={errors.fecha_inicio}
                                disableFuture                                
                            />
                        </div>    
                    </div>
                    {data.tipo_actividad === "E" && (
                        <>
                            <div className='grid w-full gap-3 items-start'>
                                <InputField
                                    label="Nombre comercial"
                                    name="e_nombre_comercial"
                                    value={data.e_nombre_comercial ?? ''}
                                    onChange={e => setData('e_nombre_comercial', e.target.value)}
                                    error={errors.e_nombre_comercial}
                                    placeholder="Especifique"
                                />
                            </div>
                            <div className='grid w-full gap-3 items-start'>
                                <InputField
                                    label="Actividad principal"
                                    name="e_actividad_principal"
                                    value={data.e_actividad_principal ?? ''}
                                    onChange={e => setData('e_actividad_principal', e.target.value)}
                                    error={errors.e_actividad_principal}
                                    placeholder="Especifique"
                                />
                            </div>
                            <div className='grid w-full gap-3 items-start'>
                                <InputField
                                    label="Actividad secundaria"
                                    name="e_actividad_secundaria"
                                    value={data.e_actividad_secundaria ?? ''}
                                    onChange={e => setData('e_actividad_secundaria', e.target.value)}
                                    error={errors.e_actividad_secundaria}
                                    placeholder="Especifique"
                                />
                            </div>
                            <div className='grid w-full gap-3 items-start'>
                                <InputField
                                    label="Sector"
                                    name="e_sector"
                                    value={data.e_sector ?? ''}
                                    onChange={e => setData('e_sector', e.target.value)}
                                    error={errors.e_sector}
                                    placeholder="Especifique"
                                />
                            </div>
                            <div className='grid w-full gap-3 items-start'>
                                <InputField
                                    label="Dirección"
                                    name="e_direccion"
                                    value={data.e_direccion ?? ''}
                                    onChange={e => setData('e_direccion', e.target.value)}
                                    error={errors.e_direccion}
                                    placeholder="Especifique"
                                />
                            </div>                            
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>  
                                <div className="grid w-full gap-3 items-start">
                                    <InputField
                                        label="Teléfonos"
                                        name="e_telefonos"
                                        value={data.e_telefonos ?? ''}
                                        onChange={e => setData('e_telefonos', e.target.value)}
                                        error={errors.e_telefonos}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3 items-start">
                                    <InputField
                                        label="Clave catastral"
                                        name="e_clave_catastral"
                                        value={data.e_clave_catastral ?? ''}
                                        onChange={e => setData('e_clave_catastral', e.target.value)}
                                        error={errors.e_clave_catastral}
                                        placeholder="Especifique"
                                    />
                                </div>
                            </div>
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm font-semibold leading-none">
                                        Geolocalización
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">                                                          
                                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                        <div className="grid w-full gap-3">
                                            <InputField
                                                label="Latitud"
                                                name="latitud"
                                                type='latitud'
                                                value={data.e_latitud ?? ''}
                                                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                    setData(
                                                        'e_latitud',
                                                        e.target.value === '' ? null : Number(e.target.value)
                                                    )
                                                }
                                                error={errors.e_latitud}
                                                placeholder="Ej: -0.180653"
                                            />
                                        </div>
                                        <div className='grid w-full gap-3 items-start'>
                                            <InputField
                                                label="Longitud"
                                                name="longitud"
                                                type='longitud'
                                                value={data.e_longitud ?? ''}
                                                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                    setData(
                                                        'e_longitud',
                                                        e.target.value === '' ? null : Number(e.target.value)
                                                    )
                                                }
                                                error={errors.e_longitud}
                                                placeholder="Ej: -78.467834"
                                            />
                                        </div>
                                    </div>
                                    <MapPicker
                                        lat={data.e_latitud}
                                        lng={data.e_longitud}
                                        onChange={(lat, lng) => {
                                            setData('e_latitud', lat);
                                            setData('e_longitud', lng);
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
                        </>
                    )}   
                    {data.tipo_actividad === "V" && (
                        <>  
                            <div className='grid w-full gap-3 items-start'>
                                <SearchableSelectField
                                    label="Compañia de transportes"
                                    name="v_compania_id"
                                    value={data.v_compania_id}
                                    options={companias ?? []}
                                    error={errors.v_compania_id}
                                    onChange={(value) => {
                                        setData("v_compania_id", value === null ? null : Number(value));
                                    }}
                                />
                            </div>            
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>                        
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Placa"
                                        name="v_placa"
                                        value={data.v_placa ?? ''}
                                        onChange={e => setData('v_placa', e.target.value)}
                                        error={errors.v_placa}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3 items-start">
                                    <InputField
                                        label="Marca"
                                        name="v_marca"
                                        value={data.v_marca ?? ''}
                                        onChange={e => setData('v_marca', e.target.value)}
                                        error={errors.v_marca}
                                        placeholder="Especifique"
                                    />
                                </div>
                            </div>
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                <div className="grid w-full gap-3 items-start">
                                    <InputField
                                        label="Clase"
                                        name="v_clase"
                                        value={data.v_clase ?? ''}
                                        onChange={e => setData('v_clase', e.target.value)}
                                        error={errors.v_clase}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Color"
                                        name="v_color"
                                        value={data.v_color ?? ''}
                                        onChange={e => setData('v_color', e.target.value)}
                                        error={errors.v_color}
                                        placeholder="Especifique"
                                    />
                                </div>
                            </div>
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Tipo"
                                        name="v_tipo"
                                        value={data.v_tipo ?? ''}
                                        onChange={e => setData('v_tipo', e.target.value)}
                                        error={errors.v_tipo}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <NumberInput
                                        label="Cilindraje"
                                        name="v_cilindraje"
                                        value={data.v_cilindraje ?? 0}
                                        onChange={(value) => setData('v_cilindraje', value)}
                                        min={1}
                                        max={100000}
                                        error={errors.v_cilindraje}
                                    />
                                </div>
                            </div>
                            <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                                <div className="grid w-full gap-1 relative">
                                    <Label htmlFor="posicion_firmante" className="mb-2">Combustible</Label>
                                    <div className="relative">      
                                        <select
                                            name="v_combustible"
                                            value={data.v_combustible ?? ''}
                                            onChange={(e) => setData('v_combustible', e.target.value)}
                                            className={cn(
                                                "border-input placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                                                errors.v_combustible && "border-destructive"
                                            )}
                                        >
                                            <option value={0}>Seleccione</option>
                                            <option value='GASOLINA'>GASOLINA</option>
                                            <option value='DIESEL'>DIESEL</option>
                                        </select>
                                        <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50" />
                                    </div>
                                    <InputError message={errors.v_combustible} />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Número de unidad"
                                        name="v_numero_unidad"
                                        value={data.v_numero_unidad ?? ''}
                                        onChange={e => setData('v_numero_unidad', e.target.value)}
                                        error={errors.v_numero_unidad}
                                        placeholder="Especifique"
                                    />
                                </div>
                            </div>                            
                        </>
                    )}     
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar actividad
                        </Button>                    
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href={`/clientes/${data.cliente_id}`}
                                data={{ search: data.search }}
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
