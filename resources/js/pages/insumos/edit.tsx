import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { RefreshCcwIcon, Undo2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar insumo',
        href: 'insumos/edit',
    },
];

interface Insumo {
    id: number,
    codigo: string;
    nombre: string,
    presentacion: string,
}

interface Props {
    insumo: Insumo;
    filters: {        
        search: string;       
    };
}

export default function Edit({insumo, filters}: Props) {

    const {data, setData, put, processing, errors} = useForm({
        codigo: insumo.codigo,
        nombre: insumo.nombre,
        presentacion: insumo.presentacion,
        search: filters?.search ?? '',
    });

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/insumos/${insumo.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar insumo" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleUpdate} className='space-y-4'>
                    <div className="grid grid-cols-2 gap-3">
                        <InputField
                            label="Código"
                            name="codigo"
                            value={data.codigo}
                            onChange={e => setData('codigo', e.target.value)}
                            error={errors.codigo}
                            placeholder="Especifique"
                        />
                    </div>  
                    <div className='grid w-full gap-3 items-start'>
                        <InputField
                            label="Nombre"
                            name="nombre"
                            value={data.nombre}
                            onChange={e => setData('nombre', e.target.value)}
                            error={errors.nombre}
                            placeholder="Especifique"
                        />
                    </div>                    
                    <div className='grid w-full gap-3 items-start'>
                        <InputField
                            label="Presentación"
                            name="presentacion"
                            value={data.presentacion}
                            onChange={e => setData('presentacion', e.target.value)}
                            error={errors.presentacion}
                            placeholder="Especifique"
                        />
                    </div>
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar insumo
                        </Button>   
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href='/insumos' 
                                data={{ search: filters.search }}
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