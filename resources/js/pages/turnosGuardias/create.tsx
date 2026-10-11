import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Spinner } from '@/components/ui/spinner';
import { SaveIcon, Undo2Icon } from 'lucide-react';
import InputField from '@/components/form/InputField';
import SearchableSelectField from '@/components/form/SearchableSelectField';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear turno de guardia',
        href: 'turnosguardias/create',
    },
];

interface PersonalPuesto {
    id: number;
    nombre: string;
}

interface Props {   
    personalesPuestos: PersonalPuesto[]; 
    filters: {
        search: string;
    };
}

export default function Create({personalesPuestos, filters}: Props) {   

    const { data, setData, post, processing, errors } = useForm<{
        personal_puesto_oficial_id: number;
        nombre: string;           
        search: string;
    }>({
        personal_puesto_oficial_id: 0,
        nombre: '',     
        search: filters?.search ?? '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/turnosguardias');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear turno de guardia" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={handleSubmit} className='space-y-4'>
                    <div className='grid w-full gap-3 items-start'>
                        <SearchableSelectField
                            label="Oficial de guardia"
                            name="personal_puesto_oficial_id"
                            value={data.personal_puesto_oficial_id}
                            options={personalesPuestos ?? []}
                            error={errors.personal_puesto_oficial_id}
                            onChange={(value) => setData("personal_puesto_oficial_id", Number(value))}
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
                    <div className="flex gap-2">                           
                        <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                            {!processing && <SaveIcon />} {processing && <Spinner />} Crear turno
                        </Button>
                        <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                            <Link 
                                href='/turnosguardias' 
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