import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { BanIcon, Car, MoreHorizontalIcon, Package, PencilIcon, PlusIcon, RotateCcwIcon, Trash2Icon, TriangleAlertIcon } from "lucide-react";
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
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bienes',
        href: '/bienes',
    },
];

interface Bien {
    id: number;
    codigo_inventario: string;
    tipo_bien: string;
    marca: string;
    modelo: string;
    serie: string;
    placa: string;
    identificacion: string;
    fecha_ingreso: string;
    estado: string;
    clasificacion_bien: {
        nombre: string;
    }
}

interface BienPagination {
    data: Bien[];
    links: LinkProps[];
}

interface FilterProps extends Record<string, any> {
    search: string;
    estado: string;
}

interface IndexProps {
    bienes: BienPagination;    
    filters: FilterProps;
}

export default function Index({bienes, filters: initialFilters}: IndexProps) {

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

    const [bienToCancel, setBienToCancel] = useState<number | null>(null);

    const [bienToActive, setBienToActive] = useState<number | null>(null);
    
    const [bienToDelete, setBienToDelete] = useState<number | null>(null);

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'bienes');
    
    const filterConfig: FilterConfig<FilterProps>[] = [
        {
            type: "search",
            key: "search",
            placeholder: "Buscar",
        },        
        {
            type: "select",
            key: "estado",
            options: [
                { value: "ALL", label: "TODOS" },
                { value: "USO", label: "BIENES EN USO" },
                { value: "INSERVIBLE", label: "BIENES INSERVIBLES" },
            ]
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bienes" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/bienes/create"
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
                {bienes.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Clasificación</th>
                                            <th className="py-2 px-4 text-left border-b">Marca</th>
                                            <th className="py-2 px-4 text-left border-b">Modelo</th>
                                            <th className="py-2 px-4 text-left border-b">Serie / placa</th>
                                            <th className="py-2 px-4 text-left border-b">Identificación</th>
                                            <th className="py-2 px-4 text-left border-b">Fecha ingreso</th>                                            
                                            <th className="py-2 px-4 text-left border-b">Estado</th>
                                            <th className="py-2 px-4 text-left border-b"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={8}
                                            />
                                        ) : (
                                            bienes.data.map((bien) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={bien.id}>
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="flex flex-col gap-1">
                                                            <span className="font-semibold text-gray-900">
                                                                {bien.clasificacion_bien.nombre}
                                                            </span>

                                                            <div>
                                                                <span className="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                                    {bien.codigo_inventario}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">{bien.marca}</td>
                                                    <td className="py-2 px-4 border-b">{bien.modelo}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        {bien.tipo_bien === "G" ? (
                                                            <div className="flex flex-col">
                                                                <span className="font-semibold text-gray-900">
                                                                    {bien.serie}
                                                                </span>
                                                                <span className="text-xs text-muted-foreground flex items-center gap-1">
                                                                    <Package className="w-5 h-5" />
                                                                    General
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <div className="flex flex-col">
                                                                <span className="font-semibold text-gray-900">
                                                                    {bien.placa}
                                                                </span>
                                                                <span className="text-xs text-muted-foreground flex items-center gap-1">
                                                                    <Car className="w-5 h-5" />
                                                                    Véhículo
                                                                </span>
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="py-2 px-4 border-b">{bien.identificacion}</td>
                                                    <td className="py-2 px-4 border-b">{dayjs(bien.fecha_ingreso).format('YYYY-MM-DD')}</td>                                                   
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${
                                                                    bien.estado === "BUENO"
                                                                        ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : bien.estado === "REGULAR"
                                                                        ? "bg-yellow-600/10 text-yellow-700 dark:bg-yellow-400/10 dark:text-yellow-400"
                                                                    : bien.estado === "MALO"
                                                                        ? "bg-orange-600/10 text-orange-700 dark:bg-orange-400/10 dark:text-orange-400"
                                                                    : bien.estado === "OBSOLETO"
                                                                        ? "bg-gray-600/10 text-gray-700 dark:bg-gray-400/10 dark:text-gray-300"
                                                                    : bien.estado === "PARA BAJA"
                                                                        ? "bg-red-600/10 text-red-600 dark:bg-red-400/10 dark:text-red-400"
                                                                    : "bg-red-800/10 text-red-800 dark:bg-red-500/10 dark:text-red-500"
                                                                }
                                                            `}
                                                        >
                                                            {bien.estado}
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
                                                                    {bien.estado !== 'INACTIVO' && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/bienes/${bien.id}/edit`}   
                                                                                    data={{ search: filters.search }}                                                                 
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>                                                 
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setBienToCancel(bien.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <BanIcon className="h-4 w-4" />
                                                                                <span>Inactivar</span>
                                                                            </DropdownMenuItem>
                                                                            
                                                                                <DropdownMenuItem
                                                                                    variant="destructive"
                                                                                    onClick={() => setBienToDelete(bien.id)}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <Trash2Icon className="h-4 w-4" />
                                                                                    Eliminar
                                                                                </DropdownMenuItem>       
                                                                                                                                               
                                                                        </>
                                                                    )}
                                                                    {bien.estado === 'INACTIVO' && (
                                                                        <DropdownMenuItem
                                                                            onClick={() => setBienToActive(bien.id)}
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
                            <Pagination links={bienes.links} />
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
                open={bienToDelete !== null}
                onOpenChange={() => setBienToDelete(null)}
                title="¿Está seguro?"
                description="Esta acción no se puede deshacer. La bien será eliminado permanentemente."
                confirmText="Eliminar"
                cancelText="Cancelar"
                variant="delete"
                onConfirm={() => {
                    if (bienToDelete) {
                        router.delete(`/bienes/${bienToDelete}`, { preserveScroll: true });
                        setBienToDelete(null);
                    }
                }}
            />
        </AppLayout>
    );
}