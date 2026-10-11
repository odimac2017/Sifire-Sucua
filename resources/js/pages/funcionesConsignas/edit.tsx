import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { RefreshCcwIcon, Undo2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Editar función para consigna',
        href: 'funcionesconsignas/edit',
    },
];

interface FuncionConsigna {
    id: number,    
    nombre: string,
}

interface Props {    
    funcionConsigna: FuncionConsigna;
    filters: {        
        search: string;       
    };
}

export default function Edit({funcionConsigna, filters}: Props) {

    const {data, setData, put, processing, errors} = useForm({        
        nombre: funcionConsigna.nombre,
        search: filters?.search ?? '',
    });

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/funcionesconsignas/${funcionConsigna.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar función para consigna" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleUpdate} className='space-y-4'>                    
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
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <RefreshCcwIcon />} {processing && <Spinner />} Actualizar función
                        </Button>   
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href='/funcionesconsignas' 
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