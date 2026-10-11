import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { BanIcon, MoreHorizontalIcon, PencilIcon, PlusIcon, RotateCcwIcon, Trash2Icon, TriangleAlertIcon } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import ConfirmDialog from '@/components/form/ConfirmDialog';
import FlashAlert from "@/components/FlashAlert";
import { useFlashAlert } from "@/hooks/useFlashAlert";
import FiltersBar from "@/components/filters/FiltersBar";
import AutoFilters from "@/components/filters/AutoFilters";
import useFilters from "@/hooks/useFilters";
import { FilterConfig } from "@/types/filters";
import TableSkeleton from '@/components/skeletons/TableSkeleton';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consignas fijas',
        href: '/consignasfijas',
    },
];

interface ConsignaFija {
    id: number,
    nombre: string,
    estado: string,
}

interface ConsignaFijaPagination {
    data: ConsignaFija[];
    links: LinkProps[];
}

interface FilterProps extends Record<string, any> {
    search: string;
}

interface IndexProps {
    consignasFijas: ConsignaFijaPagination;    
    filters: FilterProps;
}

export default function Index({consignasFijas, filters: initialFilters}: IndexProps) {

    const { flash, show } = useFlashAlert();
    
    const [loading, setLoading] = useState(false);
    
    useEffect(() => {
        const removeStart = router.on("start", () => {
            setLoading(true);
        });

        const removeFinish = router.on("finish", () => {
            setLoading(false);
        });

        return () => {
            removeStart();
            removeFinish();
        };
    }, []);

    const [consignaFjaToCancel, setConsignaFijaToCancel] = useState<number | null>(null);

    const [consignaFijaToActive, setConsignaFijaToActive] = useState<number | null>(null);
    
    const [consignaFijaToDelete, setConsignaFijaToDelete] = useState<number | null>(null);

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'consignasfijas');
    
    const filterConfig: FilterConfig<FilterProps>[] = [
        {
            type: "search",
            key: "search",
            placeholder: "Buscar consigna",
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Consignas fijas" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/consignasfijas/create"
                                data={{ search: filters.search }}
                            >
                                <PlusIcon className="mr-1 h-4 w-4"/>
                                Nuevo
                            </Link>
                        </Button>
                    }
                >
                    <AutoFilters
                        filters={filters}
                        updateFilter={updateFilter}
                        config={filterConfig}
                    />
                </FiltersBar>     
                {consignasFijas.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Nombre</th>
                                            <th className="py-2 px-4 text-left border-b">Estado</th>
                                            <th className="py-2 px-4 text-left border-b"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={3}
                                            />
                                        ) : (
                                            consignasFijas.data.map((consigna) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={consigna.id}>
                                                    <td className="py-2 px-4 border-b">{consigna.nombre}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${consigna.estado === "ACTIVO"
                                                                    ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : "bg-red-600/10 text-red-600 dark:bg-red-400/10 dark:text-red-400"
                                                                }
                                                            `}
                                                        >
                                                        {consigna.estado}
                                                        </Badge>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <DropdownMenu modal={false}>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="outline" aria-label="Open menu" size="icon-sm">
                                                                    <MoreHorizontalIcon />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent className="w-40" align="end">
                                                                <DropdownMenuGroup>
                                                                    {consigna.estado !== 'INACTIVO' && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/funcionesconsignas/${consigna.id}/edit`}  
                                                                                    data={{ search: filters.search }}                                                                  
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>                                                 
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setConsignaFijaToCancel(consigna.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <BanIcon className="h-4 w-4" />
                                                                                <span>Inactivar</span>
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setConsignaFijaToDelete(consigna.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <Trash2Icon className="h-4 w-4" />
                                                                                Eliminar
                                                                            </DropdownMenuItem>                                                                            
                                                                        </>
                                                                    )}
                                                                    {consigna.estado === 'INACTIVO' && (
                                                                        <DropdownMenuItem
                                                                            onClick={() => setConsignaFijaToActive(consigna.id)}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <RotateCcwIcon className="h-4 w-4" />
                                                                            <span>Reactivar</span>
                                                                        </DropdownMenuItem>
                                                                    )}
                                                                </DropdownMenuGroup>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div className='my-2'>
                            <Pagination links={consignasFijas.links} />
                        </div>  
                    </>
                ) : (
                    <div className="flex justify-center my-6">
                        <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                            <TriangleAlertIcon className="h-4 w-4" />
                            <AlertTitle>No existen resultados</AlertTitle>
                            <AlertDescription className="text-destructive/80">
                                Por favor intente de nuevo con otros criterios de búsqueda.
                            </AlertDescription>
                        </Alert>
                    </div>
                )}          
            </div>

            <ConfirmDialog
                open={consignaFjaToCancel !== null}
                onOpenChange={() => setConsignaFijaToCancel(null)}
                title="¿Está seguro?"
                description="La función será inactivada."
                confirmText="Inactivar"
                cancelText="Cancelar"
                variant="cancel"
                onConfirm={() => {
                    if (consignaFjaToCancel) {
                        router.patch(`/consignasfijas/${consignaFjaToCancel}/inactivar`, {
                            preserveScroll: true,
                        });
                    }
                    setConsignaFijaToCancel(null);
                }}
            />

            <ConfirmDialog
                open={consignaFijaToActive !== null}
                onOpenChange={() => setConsignaFijaToActive(null)}
                title="¿Está seguro?"
                description="La función será reactivada."
                confirmText="Reactivar"
                cancelText="Cancelar"
                variant="active"
                onConfirm={() => {
                    if (consignaFijaToActive) {
                        router.patch(`/consignasfijas/${consignaFijaToActive}/reactivar`, {
                            preserveScroll: true,
                        });
                    }
                    setConsignaFijaToActive(null);
                }}
            />

            <ConfirmDialog
                open={consignaFijaToDelete !== null}
                onOpenChange={() => setConsignaFijaToDelete(null)}
                title="¿Está seguro?"
                description="Esta acción no se puede deshacer. La consigna será eliminada permanentemente."
                confirmText="Eliminar"
                cancelText="Cancelar"
                variant="delete"
                onConfirm={() => {
                    if (consignaFijaToDelete) {
                        router.delete(`/consignasfijas/${consignaFijaToDelete}`, { preserveScroll: true });
                        setConsignaFijaToDelete(null);
                    }
                }}
            />
        </AppLayout>
    );
}