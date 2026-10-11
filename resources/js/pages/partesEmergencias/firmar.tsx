import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { CheckCircle2Icon, MoreHorizontalIcon, Signature, TriangleAlertIcon } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { useEffect, useState } from 'react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import FlashAlert from "@/components/FlashAlert";
import { useFlashAlert } from "@/hooks/useFlashAlert";
import FiltersBar from "@/components/filters/FiltersBar";
import AutoFilters from "@/components/filters/AutoFilters";
import useFilters from "@/hooks/useFilters";
import { FilterConfig } from "@/types/filters";
import EstadoFirmaBadge from '@/pages/partesEmergencias/EstadoFirmaBadge';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import ModalFirmar from '@/pages/partesEmergencias/ModalFirmar';
import TableSkeleton from '@/components/skeletons/TableSkeleton';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Firma de partes de emergencia pendientes',
        href: '/firmas/partesemergencias',
    },
];

interface Firmante {
    orden_firma: number;
    estado_firma: string;
    personal: {
        nombre: string;
    }
}

interface Parroquia {
    id: number;
    nombre: string;
}

interface ParteEmergencia {
    id: number;
    numero: string;
    categoria1: string;
    categoria2: string;
    parroquia?: Parroquia;
    fecha_llamada: string;
    direccion: string;
    estado_firma: string;
    firmantes: Firmante[];
}

interface ParteEmergenciaPagination {
    data: ParteEmergencia[];
    links: LinkProps[];
}

interface FilterProps {
    year: number | null;
    search: string;
}

interface IndexProps {
    partesEmergencias: ParteEmergenciaPagination;
    filters: FilterProps;    
}

export default function Index({partesEmergencias, filters: initialFilters}: IndexProps) {

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

    const startYear = 2020;
    const endYear = new Date().getFullYear() + 1;

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'firmas/partesemergencias');
    
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

    const [modalFirmarOpen, setModalFirmarOpen] = useState(false);
    const [parteEmergenciaToSign, setParteEmergenciaToSign] = useState<number | null>(null);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Firmar inspecciones pendientes" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar>
                    <AutoFilters
                        filters={filters}
                        updateFilter={updateFilter}
                        config={filterConfig}
                    />
                </FiltersBar>    
                {partesEmergencias.data.length ? (
                    <>                        
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">                                            
                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                            <th className="py-2 px-4 text-left border-b">Categoría</th>
                                            <th className="py-2 px-4 text-left border-b">Parroquia / Dirección</th>
                                            <th className="py-2 px-4 text-left border-b">Firmantes</th>  
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
                                            partesEmergencias.data.map((parte) => (                                        
                                                <tr
                                                    key={parte.id}
                                                    className="odd:bg-muted/50 hover:bg-gray-50"
                                                >
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{parte.numero}</span></td>
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="flex flex-col">
                                                            <span className="font-semibold text-gray-900">
                                                                {parte.categoria1}
                                                            </span>
                                                            <span className="text-gray-900">
                                                                {parte.categoria2}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="flex flex-col">
                                                            <span className="font-semibold text-gray-900">
                                                                {parte.parroquia?.nombre}
                                                            </span>
                                                            <span className="text-gray-900">
                                                                {parte.direccion}
                                                            </span>
                                                        </div>
                                                    </td>                                             
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="flex flex-col">
                                                            {parte.firmantes
                                                                ?.sort((a, b) => a.orden_firma - b.orden_firma)
                                                                .map((firmante, index) => {

                                                                    const estado = firmante.estado_firma

                                                                    let color = "bg-gray-300"
                                                                    let ring = "ring-4 ring-gray-100"

                                                                    if (estado === "FIRMADO") {
                                                                        color = "bg-green-500 text-white"
                                                                        ring = "ring-4 ring-green-100"
                                                                    }

                                                                    if (estado === "PENDIENTE") {
                                                                        color = "bg-yellow-400 text-white"
                                                                        ring = "ring-4 ring-yellow-100"
                                                                    }

                                                                    return (
                                                                        <div key={index} className="flex items-start gap-3 relative pb-4">
                                                                            {index !== parte.firmantes.length - 1 && (
                                                                                <div className="absolute left-[10px] top-6 h-full w-px bg-gray-200"></div>
                                                                            )}
                                                                            <div
                                                                                className={`w-5 h-5 flex items-center justify-center text-[10px] font-semibold rounded-full ${color} ${ring} z-10`}
                                                                            >
                                                                                {firmante.orden_firma}
                                                                            </div>
                                                                            <div className="flex flex-1 justify-between items-center">
                                                                                <span className="text-xs text-gray-800">
                                                                                    {firmante.personal.nombre}
                                                                                </span>
                                                                                <EstadoFirmaBadge
                                                                                    parteEmergencia={{ estado_firma: firmante.estado_firma }}
                                                                                />
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                    </td>                               
                                                    <td className="py-2 px-4 border-b">                                                        
                                                        <EstadoFirmaBadge parteEmergencia={parte} />                                                        
                                                    </td>
                                                    <td className="text-center border-b">
                                                        <DropdownMenu modal={false}>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="outline" aria-label="Open menu" size="icon-sm">
                                                                    <MoreHorizontalIcon />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent className="w-48" align="end">
                                                                <DropdownMenuGroup>                                                                    
                                                                    <DropdownMenuItem
                                                                        onClick={() => {                                                                            
                                                                            setParteEmergenciaToSign(parte.id);
                                                                            setModalFirmarOpen(true);                                                                           
                                                                        }}
                                                                        className="flex items-center gap-2 cursor-pointer"
                                                                    >
                                                                        <Signature className="h-4 w-4" />
                                                                        Firmar
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
                            <Pagination links={partesEmergencias.links} />
                        </div>
                    </>
                ) : (
                    <div className="flex justify-center my-4">
                        <Alert
                            className={`w-full sm:w-1/2 lg:w-1/2 ${
                                filters.search && filters.search.trim() !== ""
                                    ? "bg-red-50 text-red-700 border border-red-200"
                                    : "bg-emerald-50 text-emerald-700 border border-emerald-200"
                            }`}
                        >
                            {filters.search && filters.search.trim() !== "" ? (
                                <>
                                    <TriangleAlertIcon className="h-4 w-4" />
                                    <AlertTitle>No existen resultados</AlertTitle>
                                    <AlertDescription className="text-destructive/80">
                                        Por favor intente de nuevo con otros criterios de búsqueda.
                                    </AlertDescription>
                                </>
                            ) : (
                                <>
                                    <CheckCircle2Icon className="h-4 w-4 text-emerald-600" />
                                    <AlertTitle>No hay documentos pendientes</AlertTitle>
                                    <AlertDescription className="text-emerald-700/80">
                                        Actualmente no tiene documentos pendientes de firma.
                                    </AlertDescription>
                                </>
                            )}
                        </Alert>
                    </div>                 
                )}            
            </div>            

            <ModalFirmar
                open={modalFirmarOpen}
                onClose={() => {
                    setModalFirmarOpen(false);
                    setParteEmergenciaToSign(null);
                }}
                parteId={parteEmergenciaToSign}
                onSuccess={() => {
                    // 🔄 recargar datos sin perder filtros
                    router.reload();
                }}
            />
        </AppLayout>
    );
}
