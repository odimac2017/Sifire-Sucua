import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { CarIcon, ChevronDownIcon, Image, ImagePlus, Package, SaveIcon, Undo2Icon, X } from 'lucide-react';
import InputField from '@/components/form/InputField';
import TextAreaField from '@/components/form/TextAreaField';
import { Input } from '@/components/ui/input';
import { useMemo } from 'react';
import SelectField from '@/components/form/SelectField';
import DateField from '@/components/form/DateField';
import dayjs from 'dayjs';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { cn } from '@/lib/utils';
import { Switch } from '@/components/ui/switch';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear bien',
        href: 'bienes/create',
    },
];

interface ClasificacionBien {
    id: number;
    nombre: string;
}

interface Props {
    clasificacionesBienes: ClasificacionBien[];
    filters: {        
        search: string;
        estado: string;        
    };
}

export default function Create({clasificacionesBienes, filters}: Props) {

    const { data, setData, post, processing, errors } = useForm<{
        clasificacion_bien_id: number;
        codigo_inventario: string | null;
        tipo_bien: string;
        marca: string;
        modelo: string;
        serie: string | null;
        identificacion: string;
        placa: string | null;
        numero_chasis: string | null;
        numero_motor: string | null;
        cilindraje: string | null; 
        anio_modelo: string | null;
        clase_vehiculo: string | null;
        tipo_vehiculo: string | null;
        pais_origen: string | null;
        combustible: string | null;
        color1: string;
        color2: string;
        caracteristicas: string;
        fecha_ingreso: string;
        vida_util: string;
        interviene_emergencia: boolean;
        control_combustible: boolean;
        observaciones: string;
        estado_bien: string;
        foto: File | null;
        search: string;
        estado: string;
    }>({
        clasificacion_bien_id: 0,
        codigo_inventario: null,        
        tipo_bien: 'G',
        marca: '',
        modelo: '',
        serie: null,
        identificacion: '',
        placa: null,
        numero_chasis: null,
        numero_motor: null,
        cilindraje: null, 
        anio_modelo: null,
        clase_vehiculo: null,
        tipo_vehiculo: null,
        pais_origen: null,
        combustible: null,
        color1: '',
        color2: '',
        caracteristicas: '',
        fecha_ingreso: dayjs().format("YYYY-MM-DD"),
        vida_util: '',
        interviene_emergencia: false,
        control_combustible: false,
        observaciones: '',
        estado_bien: 'BUENO',
        foto: null as File | null,
        search: filters?.search ?? '',
        estado: filters?.estado ?? '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/bienes');
    };

    const previewFoto = useMemo(() => {
        return data.foto ? URL.createObjectURL(data.foto) : null;
    }, [data.foto]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear bien" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleSubmit} className='space-y-4'>
                    <RadioGroup
                        value={data.tipo_bien}
                        onValueChange={(value) => {
                            if (value === "G") {
                                // Limpia campos de vehículo
                                setData(prev => ({
                                    ...prev,
                                    tipo_bien: value,
                                    placa: null,
                                    numero_chasis: null,
                                    numero_motor: null,
                                    cilindraje: null, 
                                    anio_modelo: null,
                                    clase_vehiculo: null,
                                    tipo_vehiculo: null,
                                    pais_origen: null,
                                    combustible: null,
                                }))
                            }
                            if (value === "V") {
                                // Limpia campos de general
                                setData(prev => ({
                                    ...prev,
                                    tipo_bien: value,
                                    serie: null,
                                }))
                            }
                        }}
                        className="mx-auto grid w-full max-w-md grid-cols-1 gap-4 sm:grid-cols-2"
                    >
                        <div className="border-input has-data-[state=checked]:border-primary/50 relative flex flex-col items-center gap-3 rounded-md border p-4 shadow-xs outline-none">
                            <RadioGroupItem
                                value="G"
                                id="tipo-1"
                                className="order-1 size-5 after:absolute after:inset-0 [&_svg]:size-3 cursor-pointer"
                            />
                            <div className="grid justify-items-center gap-2">
                                <Package />
                                <Label htmlFor="tipo-1">General</Label>
                            </div>
                        </div>
                        <div className="border-input has-data-[state=checked]:border-primary/50 relative flex flex-col items-center gap-3 rounded-md border p-4 shadow-xs outline-none">
                            <RadioGroupItem
                                value="V"
                                id="tipo-2"
                                className="order-1 size-5 after:absolute after:inset-0 [&_svg]:size-3 cursor-pointer"
                            />
                            <div className="grid justify-items-center gap-2">
                                <CarIcon />
                                <Label htmlFor="tipo-2">Vehículo</Label>
                            </div>
                        </div>
                    </RadioGroup>
                    <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-start'>
                        <div className="grid w-full gap-3">
                            <SelectField
                                label="Clasificación del bien"
                                name="clasificacion_bien_id"
                                value={data.clasificacion_bien_id}
                                options={clasificacionesBienes}
                                error={errors.clasificacion_bien_id}
                                onChange={(value) => setData("clasificacion_bien_id", Number(value))}
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <InputField
                                label="Código de inventario"
                                name="codigo_inventario"
                                value={data.codigo_inventario ?? ''}
                                onChange={e => setData('codigo_inventario', e.target.value)}
                                error={errors.codigo_inventario}
                                placeholder="Especifique"
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <InputField
                                label="Marca"
                                name="marca"
                                value={data.marca ?? ''}
                                onChange={e => setData('marca', e.target.value)}
                                error={errors.marca}
                                placeholder="Especifique"
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <InputField
                                label=" Modelo"
                                name="modelo"
                                value={data.modelo ?? ''}
                                onChange={e => setData('modelo', e.target.value)}
                                error={errors.modelo}
                                placeholder="Especifique"
                            />
                        </div>
                        {data.tipo_bien === "G" && (                            
                            <div className="grid w-full gap-3">
                                <InputField
                                    label="Serie"
                                    name="serie"
                                    value={data.serie ?? ''}
                                    onChange={e => setData('serie', e.target.value)}
                                    error={errors.serie}
                                    placeholder="Especifique"
                                />
                            </div> 
                        )}
                        <div className="grid w-full gap-3">
                            <InputField
                                label="Identificación"
                                name="identificacion"
                                value={data.identificacion ?? ''}
                                onChange={e => setData('identificacion', e.target.value)}
                                error={errors.identificacion}
                                placeholder="Especifique"
                            />
                        </div>
                        {data.tipo_bien === "V" && (
                            <>                                
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Placa"
                                        name="placa"
                                        value={data.placa ?? ''}
                                        onChange={e => setData('placa', e.target.value)}
                                        error={errors.placa}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Número de chasis"
                                        name="numero_chasis"
                                        value={data.numero_chasis ?? ''}
                                        onChange={e => setData('numero_chasis', e.target.value)}
                                        error={errors.numero_chasis}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Número de motor"
                                        name="numero_motor"
                                        value={data.numero_motor ?? ''}
                                        onChange={e => setData('numero_motor', e.target.value)}
                                        error={errors.numero_motor}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Cilindraje"
                                        name="cilindraje"
                                        value={data.cilindraje ?? ''}
                                        onChange={e => setData('cilindraje', e.target.value)}
                                        error={errors.cilindraje}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Año modelo"
                                        name="anio_modelo"
                                        value={data.anio_modelo ?? ''}
                                        onChange={e => setData('anio_modelo', e.target.value)}
                                        error={errors.anio_modelo}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Clase de vehículo"
                                        name="clase_vehiculo"
                                        value={data.clase_vehiculo ?? ''}
                                        onChange={e => setData('clase_vehiculo', e.target.value)}
                                        error={errors.clase_vehiculo}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="Tipo de vehículo"
                                        name="tipo_vehiculo"
                                        value={data.tipo_vehiculo ?? ''}
                                        onChange={e => setData('tipo_vehiculo', e.target.value)}
                                        error={errors.tipo_vehiculo}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <InputField
                                        label="País de origen"
                                        name="pais_origen"
                                        value={data.pais_origen ?? ''}
                                        onChange={e => setData('pais_origen', e.target.value)}
                                        error={errors.pais_origen}
                                        placeholder="Especifique"
                                    />
                                </div>
                                <div className="grid w-full gap-3">
                                    <div className="grid w-full gap-1">
                                        <Label className="mb-2">Tipo de combustible</Label>
                                        <div className="relative">
                                            <select
                                                value={data.combustible ?? ''}
                                                onChange={(e) => {
                                                    const tipo = e.target.value;

                                                    setData({
                                                        ...data, 
                                                        combustible: e.target.value,                                                       
                                                    });
                                                }}
                                                className={cn(
                                                    "border-input flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs",
                                                    errors.combustible && "border-destructive"
                                                )}
                                            >
                                                <option value="">Seleccione</option>
                                                <option value="GASOLINA">GASOLINA</option>
                                                <option value="DIÉSEL">DIÉSEL</option>
                                            </select>
                                            <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50"/>
                                        </div>
                                    </div>
                                    <InputError message={errors.combustible} />
                                </div>
                            </>
                        )}
                        <div className="grid w-full gap-3">
                            <InputField
                                label="Color 1"
                                name="color1"
                                value={data.color1 ?? ''}
                                onChange={e => setData('color1', e.target.value)}
                                error={errors.color1}
                                placeholder="Especifique"
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <InputField
                                label="Color 2"
                                name="color2"
                                value={data.color2 ?? ''}
                                onChange={e => setData('color2', e.target.value)}
                                error={errors.color2}
                                placeholder="Especifique"
                            />
                        </div>                            
                        <div className='grid w-full gap-3 items-start md:col-span-2'> 
                            <TextAreaField
                                label="Características"
                                name="caracteristicas"
                                value={data.caracteristicas}
                                maxLength={300}
                                placeholder="Especifique"
                                error={errors.caracteristicas}
                                onChange={value => setData('caracteristicas', value)}
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <DateField
                                label="Fecha de ingreso"
                                value={data.fecha_ingreso}
                                onChange={(value) => setData("fecha_ingreso", value)}
                                error={errors.fecha_ingreso}
                                disableFuture                           
                            />
                        </div>
                        <div className="grid w-full gap-3">
                            <div className="grid w-full gap-1">
                                <Label className="mb-2">Vida útil</Label>
                                <div className="relative">
                                    <select
                                        value={data.vida_util}
                                        onChange={(e) => {
                                            const tipo = e.target.value;

                                            setData({
                                                ...data, 
                                                vida_util: e.target.value,                                                       
                                            });
                                        }}
                                        className={cn(
                                            "border-input flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs",
                                            errors.vida_util && "border-destructive"
                                        )}
                                    >
                                        <option value="">Seleccione</option>
                                        <option value="1 AÑO">1 AÑO</option>
                                        <option value="2 AÑOS">2 AÑOS</option>
                                        <option value="3 AÑOS">3 AÑOS</option>
                                        <option value="4 AÑOS">4 AÑOS</option>
                                        <option value="5 AÑOS">5 AÑOS</option>
                                        <option value="10 AÑOS">10 AÑOS</option>
                                        <option value="15 AÑOS">15 AÑOS</option>
                                        <option value="20 AÑOS">20 AÑOS</option>
                                        <option value="25 AÑOS">25 AÑOS</option>
                                        <option value="30 AÑOS">30 AÑOS</option>
                                    </select>
                                    <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50"/>
                                </div>
                            </div>
                            <InputError message={errors.vida_util} />
                        </div>
                        <div className="grid w-full gap-3">
                            <div className="grid w-full gap-1">
                                <Label className="mb-2">Estado</Label>
                                <div className="relative">
                                    <select
                                        value={data.estado_bien}
                                        onChange={(e) => {
                                            const tipo = e.target.value;

                                            setData({
                                                ...data, 
                                                estado_bien: e.target.value,                                                       
                                            });
                                        }}
                                        className={cn(
                                            "border-input flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs",
                                            errors.estado_bien && "border-destructive"
                                        )}
                                    >
                                        <option value="">Seleccione</option>
                                        <option value="BUENO">BUENO</option>
                                        <option value="REGULAR">REGULAR</option>
                                        <option value="MALO">MALO</option>
                                        <option value="OBSOLETO">OBSOLETO</option>
                                        <option value="PARA BAJA">PARA BAJA</option>
                                        <option value="BAJA">BAJA</option>                                       
                                    </select>
                                    <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50"/>
                                </div>
                            </div>
                            <InputError message={errors.estado_bien} />
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-2 gap-3 items-center md:col-span-2'>
                            <div className="flex items-center gap-2">
                                <Switch
                                    id="interviene_emergencia"
                                    checked={data.interviene_emergencia}
                                    onCheckedChange={(checked) =>
                                        setData('interviene_emergencia', checked)
                                    }
                                />
                                <Label htmlFor="interviene_emergencia">Interviene en atenciones de emergencias</Label>
                            </div>
                            <div className="flex items-center gap-2">
                                <Switch
                                    id="control_combustible"
                                    checked={data.control_combustible}
                                    onCheckedChange={(checked) =>
                                        setData('control_combustible', checked)
                                    }
                                />
                                <Label htmlFor="control_combustible">Considerado para control de combustible</Label>
                            </div>
                        </div>
                        <div className='grid w-full gap-3 items-start md:col-span-2'> 
                            <TextAreaField
                                label="Observaciones"
                                name="observaciones"
                                value={data.observaciones}
                                maxLength={200}
                                placeholder="Especifique"
                                error={errors.observaciones}
                                onChange={value => setData('observaciones', value)}
                            />
                        </div>
                        <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                            <Image className="w-4 h-4" />
                            <span className="text-sm font-semibold">
                                Foto
                            </span>
                            <div className="flex-1 h-px bg-border ml-2"></div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                            <div className="grid w-full gap-2 md:col-span-2">                                
                                <div className="border border-dashed border-border rounded-xl p-4 bg-muted/30 flex min-h-[300px] items-center justify-center">                                    
                                    {!previewFoto ? (
                                        <label
                                            htmlFor="foto"
                                            className="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-2 text-center"
                                        >
                                            <ImagePlus className="w-10 h-10 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm font-medium">
                                                    Seleccionar imagen
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    JPG, PNG o WEBP
                                                </p>
                                            </div>
                                            <Input
                                                id="foto"
                                                type="file"
                                                accept="image/*"
                                                className="hidden"
                                                onChange={(e) =>
                                                    setData(
                                                        "foto",
                                                        e.target.files?.[0] ?? null
                                                    )
                                                }
                                            />
                                        </label>
                                    ) : (
                                        <div className="relative">                                            
                                            <img
                                                src={previewFoto}
                                                alt="Vista previa"
                                                className="h-64 w-full rounded-lg object-cover border"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setData("foto", null)}
                                                className="absolute right-2 top-2 rounded-md bg-black/70 p-1 text-white hover:bg-black cursor-pointer"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                            <div className="mt-2 text-xs text-muted-foreground">
                                                {data.foto?.name}
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <InputError message={errors.foto} />
                            </div>
                        </div>
                    </div>
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <SaveIcon />} {processing && <Spinner />} Crear bien
                        </Button>                    
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href="/bienes" 
                                data={{ search: data.search, estado: data.estado }}
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