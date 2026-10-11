import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { CheckCircle, DownloadIcon, FoldersIcon, MoreHorizontalIcon, PencilIcon, PlusIcon, Trash2Icon, TriangleAlertIcon, XCircle } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useEffect, useState } from 'react';
import ConfirmDialog from "@/components/form/ConfirmDialog";
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import FlashAlert from "@/components/FlashAlert";
import { useFlashAlert } from "@/hooks/useFlashAlert";
import FiltersBar from "@/components/filters/FiltersBar";
import AutoFilters from "@/components/filters/AutoFilters";
import useFilters from "@/hooks/useFilters";
import { FilterConfig } from "@/types/filters";
import TableSkeleton from '@/components/skeletons/TableSkeleton';
import { Badge } from '@/components/ui/badge';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Partes de consignas',
        href: '/partesconsignas',
    },
];

interface Consigna {
    id: number;
    nombre_personal: string;
    consigna: string;
    cumple: number;
}

interface ParteConsigna {
    id: number,
    personal_entrega_id: number;
    fecha: string,
    nombre_entrega: string,
    novedades: string;
    estado: string;
    consignas: Consigna[];
    turno_guardia: {
        nombre: string;
    }
}

interface ParteConsignaPagination {
    data: ParteConsigna[];
    links: LinkProps[];
}

interface FilterProps {
    year: number | null;
    search: string;
}

interface IndexProps {
    partesConsignas: ParteConsignaPagination;
    auth_personal_id: number;
    filters: FilterProps;
}

export default function Index({partesConsignas, auth_personal_id, filters: initialFilters }: IndexProps) {    
console.log(partesConsignas);
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

    const [parteConsignaToDelete, setParteConsignaToDelete] = useState<number | null>(null);

    const startYear = 2020;
    const endYear = new Date().getFullYear() + 1;

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'partesconsignas');
    
    const filterConfig: FilterConfig<FilterProps>[] = [
        {
            type: "search",
            key: "search",
            placeholder: "Buscar",
        },
        {
            type: "year",
            key: "year",
            fromYear: startYear,
            toYear: endYear,
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Partes de consignas" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar>
                    <AutoFilters
                        filters={filters}
                        updateFilter={updateFilter}
                        config={filterConfig}
                    />
                </FiltersBar>  
                {partesConsignas.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="w-28 py-2 px-4 text-left border-b">Fecha</th>
                                            <th className="w-28 py-2 px-4 text-left border-b">Turno</th>
                                            <th className="w-50 py-2 px-4 text-left border-b">Oficial guardia</th>
                                            <th className="w-80 py-2 px-4 text-left border-b">Consignas</th>
                                            <th className="w-80 py-2 px-4 text-left border-b">Novedades</th>
                                            <th className="w-24 py-2 px-4 text-left border-b">Estado</th>
                                            <th className="w-10 py-2 px-4 text-left border-b"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={7}
                                            />
                                        ) : (
                                            partesConsignas.data.map((parte) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={parte.id}>
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{dayjs(parte.fecha).format('YYYY-MM-DD')}</span></td>
                                                    <td className="py-2 px-4 border-b">{parte.turno_guardia.nombre}</td>
                                                    <td className="py-2 px-4 border-b">{parte.nombre_entrega}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        {parte.consignas?.map((consigna, index) => (
                                                            <div key={index} className="flex items-start gap-2 mb-2">
                                                                <span className="mt-1">
                                                                    {consigna.cumple == 1 ? (
                                                                        <CheckCircle size={18} className="text-green-600" />
                                                                    ) : (
                                                                        <XCircle size={18} className="text-red-600" />
                                                                    )}
                                                                </span>
                                                                <div className="leading-tight">
                                                                    <div className="font-medium">
                                                                        {consigna.consigna}
                                                                    </div>
                                                                    <div className="text-xs text-gray-500">
                                                                        {consigna.nombre_personal}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </td>
                                                    <td className="py-2 px-4 border-b">{parte.novedades}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${parte.estado === "ABIERTO"
                                                                    ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : "bg-red-600/10 text-red-600 dark:bg-red-400/10 dark:text-red-400"
                                                                }
                                                            `}
                                                        >
                                                        {parte.estado}
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
                                                                    {parte.estado === 'ABIERTO' && parte.personal_entrega_id === auth_personal_id && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/partesconsignas/${parte.id}/edit`}
                                                                                    data={{ search: filters.search, year: filters.year }}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setParteConsignaToDelete(parte.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <Trash2Icon className="h-4 w-4" />
                                                                                Eliminar
                                                                            </DropdownMenuItem>
                                                                        </>
                                                                    )}                                                                    
                                                                    <DropdownMenuItem>
                                                                        <a
                                                                            href={`/partesconsignas/${parte.id}/descargar`}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <DownloadIcon className="h-4 w-4" />
                                                                            <span>Descargar PDF</span>
                                                                        </a>
                                                                    </DropdownMenuItem>
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
                            <Pagination links={partesConsignas.links} />
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
                open={parteConsignaToDelete !== null}
                onOpenChange={() => setParteConsignaToDelete(null)}
                title="¿Está seguro?"
                description="Esta acción no se puede deshacer. El parte diario de consignas será eliminado permanentemente."
                confirmText="Eliminar"
                cancelText="Cancelar"
                variant="delete"
                onConfirm={() => {
                    if (parteConsignaToDelete) {
                        router.delete(`/partesconsignas/${parteConsignaToDelete}`, {
                            preserveScroll: true,
                        });
                    }
                    setParteConsignaToDelete(null);
                }}
            />
        </AppLayout>
    );
}
