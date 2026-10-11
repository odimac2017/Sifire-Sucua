import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { Clock, DownloadIcon, FoldersIcon, MapPin, MoreHorizontalIcon, PencilIcon, PlusIcon, Trash2Icon, TriangleAlertIcon } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useEffect, useState } from 'react';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import FlashAlert from "@/components/FlashAlert";
import { useFlashAlert } from "@/hooks/useFlashAlert";
import FiltersBar from "@/components/filters/FiltersBar";
import AutoFilters from "@/components/filters/AutoFilters";
import useFilters from "@/hooks/useFilters";
import { FilterConfig } from "@/types/filters";
import EstadoFirmaBadge from '@/pages/informesDiarios/EstadoFirmaBadge';
import ModalFirmar from '@/pages/informesDiarios/ModalFirmar';
import TableSkeleton from '@/components/skeletons/TableSkeleton';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Informes diarios',
        href: '/informesdiarios',
    },
];

interface InformeDiario {
    id: number;
    personal_id: number;
    nombre_personal: string;
    numero: string;
    fecha_informe: string;
    total_general: number;
    estado_firma: string;
}

interface InformeDiarioPagination {
    data: InformeDiario[];
    links: LinkProps[];
}

interface FilterProps {
    year: number | null;
    search: string;
}

interface IndexProps {
    informesDiarios: InformeDiarioPagination;
    auth_personal_id: number;
    filters: FilterProps;
}

export default function Index({informesDiarios, auth_personal_id, filters: initialFilters }: IndexProps) {    

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

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'informesdiarios');
    
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
    const [informeDiarioToSign, setInformeDiarioToSign] = useState<number | null>(null);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Partes emergencias" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/informesdiarios/create"
                                data={{ search: filters.search, year: filters.year  }}
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
                {informesDiarios.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                            <th className="py-2 px-4 text-left border-b">Fecha</th>
                                            <th className="py-2 px-4 text-left border-b">Total</th>
                                            <th className="py-2 px-4 text-left border-b">Personal responsable</th>
                                            <th className="py-2 px-4 text-left border-b">Estado Firma</th>
                                            <th className="py-2 px-4 text-left border-b"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {loading ? (
                                            <TableSkeleton
                                                rows={8}
                                                columns={7}
                                            />
                                        ) : (
                                            informesDiarios.data.map((informe) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={informe.id}>
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{informe.numero}</span></td>
                                                    <td className="py-2 px-4 border-b">{dayjs(informe.fecha_informe).format('YYYY-MM-DD')}</td>
                                                    <td className="py-2 px-4 border-b">{informe.total_general}</td>
                                                    <td className="py-2 px-4 border-b">{informe.nombre_personal}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <EstadoFirmaBadge informeDiario={informe} />
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
                                                                    {informe.personal_id === auth_personal_id && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/informesdiarios/${informe.id}/edit`}
                                                                                    data={{ search: filters.search, year: filters.year }}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>                                                                            
                                                                            {['ERROR_FIRMA', 'PENDIENTE', 'REQUIERE_PASSWORD'].includes(informe.estado_firma) && (
                                                                                <DropdownMenuItem
                                                                                    onClick={() => {
                                                                                        if (informe.estado_firma === 'REQUIERE_PASSWORD') {
                                                                                            // 👉 abrir modal
                                                                                            setInformeDiarioToSign(informe.id);
                                                                                            setModalFirmarOpen(true);
                                                                                        } else {
                                                                                            // 👉 firma automática (Job)
                                                                                            router.post(`/informesdiarios/${informe.id}/firmar`);
                                                                                        }
                                                                                    }}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <Clock className="h-4 w-4" />
                                                                                    <span>
                                                                                        {informe.estado_firma === 'ERROR_FIRMA'
                                                                                            ? 'Reintentar firma'
                                                                                            : informe.estado_firma === 'REQUIERE_PASSWORD'
                                                                                            ? 'Firmar con contraseña'
                                                                                            : 'Firmar'}
                                                                                    </span>
                                                                                </DropdownMenuItem>
                                                                            )}
                                                                        </>
                                                                    )}                                                                    
                                                                    {['SIN_FIRMA','FIRMADO'].includes(informe.estado_firma) && (                                                                        
                                                                        <DropdownMenuItem>
                                                                            <a
                                                                                href={`/informesdiarios/${informe.id}/descargar`}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <DownloadIcon className="h-4 w-4" />
                                                                                <span>Descargar PDF</span>
                                                                            </a>
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
                            <Pagination links={informesDiarios.links} />
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
            <ModalFirmar
                open={modalFirmarOpen}
                onClose={() => {
                    setModalFirmarOpen(false);
                    setInformeDiarioToSign(null);
                }}
                informeId={informeDiarioToSign}
                onSuccess={() => {
                    // 🔄 recargar datos sin perder filtros
                    router.reload();
                }}
            />
        </AppLayout>
    );
}
