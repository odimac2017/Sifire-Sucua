import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { SaveIcon, Undo2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear consigna fija',
        href: 'consignasfijas/create',
    },
];

interface Props {
    filters: {
        search: string;
    };
}

export default function Create({filters}: Props) {   

    const { data, setData, post, processing, errors } = useForm<{        
        nombre: string;           
        search: string;
    }>({
        nombre: '',     
        search: filters?.search ?? '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/consignasfijas');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear consigna fija" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleSubmit} className='space-y-4'>                    
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
                            {!processing && <SaveIcon />} {processing && <Spinner />} Crear consigna
                        </Button>
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href='/consignasfijas' 
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