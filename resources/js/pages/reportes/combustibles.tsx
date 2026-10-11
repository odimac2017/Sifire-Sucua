import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { BanIcon, FileSpreadsheet, FileText, MoreHorizontalIcon, PencilIcon, PlusIcon, TriangleAlertIcon } from "lucide-react";
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
import { DateRange } from "react-day-picker";

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: ' Reporte de control de combustibles',
        href: '/reportescombustibles',
    },
];

interface Reporte {
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

interface ReportePagination {
    data: Reporte[];
    links: LinkProps[];
}

interface FilterProps {
    rango_fecha?: DateRange;
    estado: string;
}

interface IndexProps {
    reportes: ReportePagination;
    filters: FilterProps;    
}

export default function Index({reportes, filters: initialFilters}: IndexProps) {

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

    const parsedFilters: FilterProps = {
        ...initialFilters,
        rango_fecha: initialFilters.rango_fecha
            ? {
                from: initialFilters.rango_fecha.from
                    ? dayjs(initialFilters.rango_fecha.from).toDate()
                    : undefined,
                to: initialFilters.rango_fecha.to
                    ? dayjs(initialFilters.rango_fecha.to).toDate()
                    : undefined,
            }
            : undefined,
    };

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'reportescombustibles');
    
    const filterConfig: FilterConfig<FilterProps>[] = [
        {
            type: "dateRange",
            key: "rango_fecha",
            presets: true,
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
            <Head title="Reporte de control de combustibles" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <div className="flex items-center gap-2">
                            {reportes?.data?.length > 0 && (
                                <>  
                                    <Button
                                        size="sm"
                                        className="bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer"
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams({
                                                "rango_fecha[from]": filters.rango_fecha?.from
                                                    ? dayjs(filters.rango_fecha.from).format("YYYY-MM-DD")
                                                    : "",
                                                "rango_fecha[to]": filters.rango_fecha?.to
                                                    ? dayjs(filters.rango_fecha.to).format("YYYY-MM-DD")
                                                    : "",
                                            });

                                            window.open(`/reportescombustibles/exportExcel?${params.toString()}&estado=${filters.estado}`, "_blank");
                                        }}
                                    >
                                        <FileSpreadsheet className="mr-1 h-4 w-4" />
                                        Excel
                                    </Button>             
                                    <Button
                                        size="sm"
                                        className="bg-red-600 hover:bg-red-700 text-white cursor-pointer"
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams({
                                                "rango_fecha[from]": filters.rango_fecha?.from
                                                    ? dayjs(filters.rango_fecha.from).format("YYYY-MM-DD")
                                                    : "",
                                                "rango_fecha[to]": filters.rango_fecha?.to
                                                    ? dayjs(filters.rango_fecha.to).format("YYYY-MM-DD")
                                                    : "",
                                            });

                                            window.open(`/reportescombustibles/exportPdf?${params.toString()}&estado=${filters.estado}`, '_blank');
                                        }}
                                    >
                                        <FileText className="mr-1 h-4 w-4" />
                                        PDF
                                    </Button>
                                </>
                            )}
                        </div>
                    }
                >
                    <AutoFilters
                        filters={filters}
                        updateFilter={updateFilter}
                        config={filterConfig}
                    />
                </FiltersBar>
                {reportes.data.length ? (
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={9}
                                            />
                                        ) : (
                                            reportes.data.map((reporte) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={reporte.id}>
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{reporte.numero_formateado}</span></td>
                                                    <td className="py-2 px-4 border-b">{dayjs(reporte.fecha).format('YYYY-MM-DD')}</td>
                                                    <td className="py-2 px-4 border-b">{reporte.bien.identificacion}</td>
                                                    <td className="py-2 px-4 border-b">{reporte.nombre_chofer.toUpperCase()}</td>
                                                    <td className="py-2 px-4 border-b">{reporte.combustible.nombre}</td>
                                                    <td className="py-2 px-4 border-b">{reporte.km_recorrido}</td>
                                                    <td className="py-2 px-4 border-b">{reporte.cantidad_galones}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <span className="font-semibold text-slate-700">
                                                            {money(reporte.valor_total)}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground ml-1">
                                                            USD
                                                        </span>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <Badge
                                                            className={`border-none focus-visible:outline-none
                                                                ${reporte.estado === "GENERADO"
                                                                    ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                    : "bg-orange-600/10 text-orange-600 dark:bg-orange-400/10 dark:text-orange-400"
                                                                }
                                                            `}
                                                        >
                                                            {reporte.estado}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div className='my-2'>
                            <Pagination links={reportes.links} />
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
        </AppLayout>
    );
}
