import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { BanIcon, MoreHorizontalIcon, PencilIcon, PlusIcon, TriangleAlertIcon } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import FlashAlert from "@/components/FlashAlert";
import { useFlashAlert } from "@/hooks/useFlashAlert";
import FiltersBar from "@/components/filters/FiltersBar";
import AutoFilters from "@/components/filters/AutoFilters";
import useFilters from "@/hooks/useFilters";
import { FilterConfig } from "@/types/filters";
import { Badge } from '@/components/ui/badge';
import { useEffect, useState } from 'react';
import TableSkeleton from '@/components/skeletons/TableSkeleton';
import { money } from "@/utils/format";
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import ConfirmDialog from '@/components/form/ConfirmDialog';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Control de combustibles',
        href: '/controlescombustibles',
    },
];

interface ControlCombustible {
    id: number;
    numero_formateado: string;
    nombre_chofer: string;
    fecha: string;    
    km_recorrido: number;
    cantidad_galones: number;
    valor_total: number;
    estado: string;
    bien: {
        identificacion: string;
    };
    combustible: {
        nombre: string;
    }
}

interface ControlCombustiblePagination {
    data: ControlCombustible[];
    links: LinkProps[];
}

interface FilterProps {
    year: number;
    search: string;
    estado: string;
}

interface IndexProps {
    controlesCombustibles: ControlCombustiblePagination;
    filters: FilterProps;    
}

export default function Index({controlesCombustibles, filters: initialFilters}: IndexProps) {

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

    const [controlCombustibleToCancel, setControlCombustibleToCancel] = useState<number | null>(null);
    
    const startYear = 2020;
    const endYear = new Date().getFullYear() + 1;

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'controlescombustibles');
    
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
        },
        {
            type: "select",
            key: "estado",
            options: [
                { value: "ALL", label: "TODOS" },
                { value: "GENERADO", label: "GENERADOS" },
                { value: "ANULADO", label: "ANULADOS" },
            ]
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Control de combustibles" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/controlescombustibles/create"
                                data={{ search: filters.search, year: filters.year, estado: filters.estado }}
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
                {controlesCombustibles.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                            <th className="py-2 px-4 text-left border-b">Fecha</th>
                                            <th className="py-2 px-4 text-left border-b">Vehículo / equipo</th>
                                            <th className="py-2 px-4 text-left border-b">Conductor</th>
                                            <th className="py-2 px-4 text-left border-b">Combustible</th>
                                            <th className="py-2 px-4 text-left border-b">Km recorrido</th>
                                            <th className="py-2 px-4 text-left border-b">Galores</th>
                                            <th className="py-2 px-4 text-left border-b">Valor total</th>
                                            <th className="py-2 px-4 text-left border-b">Estado</th>                                
                                            <th className="py-2 px-4 text-left border-b"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={10}
                                            />
                                        ) : (
                                            controlesCombustibles.data.map((control) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={control.id}>
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{control.numero_formateado}</span></td>
                                                    <td className="py-2 px-4 border-b">{dayjs(control.fecha).format('YYYY-MM-DD')}</td>
                                                    <td className="py-2 px-4 border-b">{control.bien.identificacion}</td>
                                                    <td className="py-2 px-4 border-b">{control.nombre_chofer.toUpperCase()}</td>
                                                    <td className="py-2 px-4 border-b">{control.combustible.nombre}</td>
                                                    <td className="py-2 px-4 border-b">{control.km_recorrido}</td>
                                                    <td className="py-2 px-4 border-b">{control.cantidad_galones}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <span className="font-semibold text-slate-700">
                                                            {money(control.valor_total)}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground ml-1">
                                                            USD
                                                        </span>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${control.estado === "GENERADO"
                                                                    ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : "bg-orange-600/10 text-orange-600 dark:bg-orange-400/10 dark:text-orange-400"
                                                                }
                                                            `}
                                                        >
                                                            {control.estado}
                                                        </Badge>
                                                    </td>                        
                                                    <td className="text-center border-b">
                                                        <DropdownMenu modal={false}>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="outline" aria-label="Open menu" size="icon-sm">
                                                                    <MoreHorizontalIcon />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent className="w-40" align="end">
                                                                <DropdownMenuGroup>
                                                                    <DropdownMenuItem asChild>
                                                                        <Link
                                                                            href={`/controlescombustibles/${control.id}/edit`}
                                                                            data={{ search: filters.search, year: filters.year, estado: filters.estado }}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <PencilIcon className="h-4 w-4" />
                                                                            <span>Editar</span>
                                                                        </Link>
                                                                    </DropdownMenuItem>
                                                                    {control.estado !== 'ANULADO' && (
                                                                        <DropdownMenuItem
                                                                            variant="destructive"
                                                                            onClick={() => setControlCombustibleToCancel(control.id)}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <BanIcon className="h-4 w-4" />
                                                                            <span>Anular</span>
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
                            <Pagination links={controlesCombustibles.links} />
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
                open={controlCombustibleToCancel !== null}
                onOpenChange={() => setControlCombustibleToCancel(null)}
                title="¿Está seguro?"
                description="El control de combustible será anulado."
                confirmText="Anular"
                cancelText="Cancelar"
                variant="cancel"
                onConfirm={() => {
                    if (controlCombustibleToCancel) {
                        router.patch(`/controlescombustibles/${controlCombustibleToCancel}/anular`, {
                            preserveScroll: true,
                        });
                    }
                    setControlCombustibleToCancel(null);
                }}
            />
        </AppLayout>
    );
}
