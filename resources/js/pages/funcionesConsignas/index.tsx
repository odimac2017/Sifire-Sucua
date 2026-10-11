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
        title: 'Funciones para consignas',
        href: '/funcionesconsignas',
    },
];

interface FuncionConsigna {
    id: number,
    nombre: string,
    estado: string,
}

interface FuncionConsignaPagination {
    data: FuncionConsigna[];
    links: LinkProps[];
}

interface FilterProps extends Record<string, any> {
    search: string;
}

interface IndexProps {
    funcionesConsignas: FuncionConsignaPagination;    
    filters: FilterProps;
}

export default function Index({funcionesConsignas, filters: initialFilters}: IndexProps) {

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

    const [funcionConsignaToCancel, setFuncionConsignaToCancel] = useState<number | null>(null);

    const [funcionConsignaToActive, setFuncionConsignaToActive] = useState<number | null>(null);
    
    const [funcionConsignaToDelete, setFuncionConsignaToDelete] = useState<number | null>(null);

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'funcionesconsignas');
    
    const filterConfig: FilterConfig<FilterProps>[] = [
        {
            type: "search",
            key: "search",
            placeholder: "Buscar función",
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Funciones para consignas" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/funcionesconsignas/create"
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
                {funcionesConsignas.data.length ? (
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
                                            funcionesConsignas.data.map((funcion) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={funcion.id}>
                                                    <td className="py-2 px-4 border-b">{funcion.nombre}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${funcion.estado === "ACTIVO"
                                                                    ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : "bg-red-600/10 text-red-600 dark:bg-red-400/10 dark:text-red-400"
                                                                }
                                                            `}
                                                        >
                                                        {funcion.estado}
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
                                                                    {funcion.estado !== 'INACTIVO' && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/funcionesconsignas/${funcion.id}/edit`}
                                                                                    data={{ search: filters.search }}                                                                
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>                                                 
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setFuncionConsignaToCancel(funcion.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <BanIcon className="h-4 w-4" />
                                                                                <span>Inactivar</span>
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setFuncionConsignaToDelete(funcion.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <Trash2Icon className="h-4 w-4" />
                                                                                Eliminar
                                                                            </DropdownMenuItem>                                                                            
                                                                        </>
                                                                    )}
                                                                    {funcion.estado === 'INACTIVO' && (
                                                                        <DropdownMenuItem
                                                                            onClick={() => setFuncionConsignaToActive(funcion.id)}
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
                            <Pagination links={funcionesConsignas.links} />
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
                open={funcionConsignaToCancel !== null}
                onOpenChange={() => setFuncionConsignaToCancel(null)}
                title="¿Está seguro?"
                description="La función será inactivada."
                confirmText="Inactivar"
                cancelText="Cancelar"
                variant="cancel"
                onConfirm={() => {
                    if (funcionConsignaToCancel) {
                        router.patch(`/funcionesconsignas/${funcionConsignaToCancel}/inactivar`, {
                            preserveScroll: true,
                        });
                    }
                    setFuncionConsignaToCancel(null);
                }}
            />

            <ConfirmDialog
                open={funcionConsignaToActive !== null}
                onOpenChange={() => setFuncionConsignaToActive(null)}
                title="¿Está seguro?"
                description="La función será reactivada."
                confirmText="Reactivar"
                cancelText="Cancelar"
                variant="active"
                onConfirm={() => {
                    if (funcionConsignaToActive) {
                        router.patch(`/funcionesconsignas/${funcionConsignaToActive}/reactivar`, {
                            preserveScroll: true,
                        });
                    }
                    setFuncionConsignaToActive(null);
                }}
            />

            <ConfirmDialog
                open={funcionConsignaToDelete !== null}
                onOpenChange={() => setFuncionConsignaToDelete(null)}
                title="¿Está seguro?"
                description="Esta acción no se puede deshacer. La función será eliminada permanentemente."
                confirmText="Eliminar"
                cancelText="Cancelar"
                variant="delete"
                onConfirm={() => {
                    if (funcionConsignaToDelete) {
                        router.delete(`/funcionesconsignas/${funcionConsignaToDelete}`, { preserveScroll: true });
                        setFuncionConsignaToDelete(null);
                    }
                }}
            />
        </AppLayout>
    );
}