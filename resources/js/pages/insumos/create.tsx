import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { SaveIcon, Undo2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear insumo',
        href: 'insumos/create',
    },
];

interface Props {    
    filters: {
        search: string;
    };
}

export default function Create({filters}: Props) {   

    const { data, setData, post, processing, errors } = useForm<{
        codigo: string;
        nombre: string;
        presentacion: string;        
        search: string;
    }>({
        codigo: '',
        nombre: '',
        presentacion: '',        
        search: filters?.search ?? '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/insumos');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear insumo" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleSubmit} className='space-y-4'>
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
                            {!processing && <SaveIcon />} {processing && <Spinner />} Crear insumo
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