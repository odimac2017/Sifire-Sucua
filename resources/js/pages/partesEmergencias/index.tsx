import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, LinkProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import Pagination from '@/components/pagination';
import { Clock, DownloadIcon, FoldersIcon, MapPin, MoreHorizontalIcon, PencilIcon, PlusIcon, Trash2Icon, TriangleAlertIcon } from "lucide-react";
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
import EstadoFirmaBadge from '@/pages/partesEmergencias/EstadoFirmaBadge';
import ModalFirmar from '@/pages/partesEmergencias/ModalFirmar';
import TableSkeleton from '@/components/skeletons/TableSkeleton';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Partes emergencias',
        href: '/partesemergencias',
    },
];

interface Categoria {
    categoria1: string;
    categoria2: string;
}

interface Parroquia {
    id: number;
    nombre: string;
}

interface ParteEmergencia {
    id: number;
    personal_id: number;
    numero: string;
    parroquia?: Parroquia;
    direccion: string;
    latitud: string;
    longitud: string;
    fecha_llamada: string;
    fecha_llegada: string;
    fecha_atencion: string;
    nombre_personal: string;
    estado_firma: string;
    categorias?: Categoria[];
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
    auth_personal_id: number;
    filters: FilterProps;
}

export default function Index({partesEmergencias, auth_personal_id, filters: initialFilters }: IndexProps) {    

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

    const [parteEmergenciaToDelete, setParteEmergenciaToDelete] = useState<number | null>(null);

    const startYear = 2020;
    const endYear = new Date().getFullYear() + 1;

    const { filters, updateFilter } = useFilters<FilterProps>(initialFilters, 'partesemergencias');
    
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
            <Head title="Partes emergencias" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <FlashAlert flash={flash} show={show} />
                <FiltersBar
                    actions={
                        <Button asChild size="sm">
                            <Link
                                href="/partesemergencias/create"
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
                {partesEmergencias.data.length ? (
                    <>
                        <div className="border rounded-xl shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                            <th className="py-2 px-4 text-left border-b">Categoría</th>
                                            <th className="py-2 px-4 text-left border-b">Dirección</th>
                                            <th className="py-2 px-4 text-left border-b">Fechas</th>
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
                                            partesEmergencias.data.map((parte) => (
                                                <tr className="odd:bg-muted/50 hover:bg-gray-50" key={parte.id}>
                                                    <td className="py-2 px-4 border-b"><span className='font-medium'>{parte.numero}</span></td>
                                                    <td className="py-2 px-4 border-b">
                                                        {parte.categorias?.length ? (
                                                            <div className="flex flex-col gap-2">
                                                                {parte.categorias.map((categoria, index) => (
                                                                    <div
                                                                        key={index}
                                                                        className="flex items-center gap-2 flex-wrap"
                                                                    >
                                                                        <div className="space-y-1">
                                                                            <span className="inline-flex w-fit rounded-md bg-primary px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-primary-foreground">
                                                                                {categoria.categoria1}
                                                                            </span>

                                                                            <p className="text-sm">
                                                                                {categoria.categoria2}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground">
                                                                SIN CATEGORÍA
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="space-y-1">
                                                            <div className="font-medium">
                                                                {parte.direccion}
                                                            </div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {parte.parroquia?.nombre}
                                                            </div>
                                                            <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                                                <MapPin className="h-4 w-4" />
                                                                <span className="font-mono">
                                                                    {parte.latitud}, {parte.longitud}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">
                                                        <div className="flex items-center gap-6 text-sm">
                                                            <div className="flex flex-col">
                                                                <span className="text-muted-foreground text-xs">LLAMADA</span>
                                                                <span className="font-semibold">
                                                                    {dayjs.utc(parte.fecha_llamada).tz('America/Guayaquil').format('YYYY-MM-DD')}
                                                                </span>
                                                            </div>
                                                            <div className="flex flex-col">
                                                                <span className="text-muted-foreground text-xs">ATENCIÓN</span>
                                                                <span className="font-semibold">
                                                                    {dayjs.utc(parte.fecha_atencion).tz('America/Guayaquil').format('YYYY-MM-DD')}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="py-2 px-4 border-b">{parte.nombre_personal}</td>
                                                    <td className="py-2 px-4 border-b">
                                                        <EstadoFirmaBadge parteEmergencia={parte} />
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
                                                                    {parte.personal_id === auth_personal_id && (
                                                                        <>
                                                                            <DropdownMenuItem asChild>
                                                                                <Link
                                                                                    href={`/partesemergencias/${parte.id}/edit`}
                                                                                    data={{ search: filters.search, year: filters.year }}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <PencilIcon className="h-4 w-4" />
                                                                                    <span>Editar</span>
                                                                                </Link>
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                variant="destructive"
                                                                                onClick={() => setParteEmergenciaToDelete(parte.id)}
                                                                                className="flex items-center gap-2 cursor-pointer"
                                                                            >
                                                                                <Trash2Icon className="h-4 w-4" />
                                                                                Eliminar
                                                                            </DropdownMenuItem>

                                                                            {['ERROR_FIRMA', 'PENDIENTE', 'REQUIERE_PASSWORD'].includes(parte.estado_firma) && (
                                                                                <DropdownMenuItem
                                                                                    onClick={() => {
                                                                                        if (parte.estado_firma === 'REQUIERE_PASSWORD') {
                                                                                            // 👉 abrir modal
                                                                                            setParteEmergenciaToSign(parte.id);
                                                                                            setModalFirmarOpen(true);
                                                                                        } else {
                                                                                            // 👉 firma automática (Job)
                                                                                            router.post(`/partesemergencias/${parte.id}/firmar`);
                                                                                        }
                                                                                    }}
                                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                                >
                                                                                    <Clock className="h-4 w-4" />
                                                                                    <span>
                                                                                        {parte.estado_firma === 'ERROR_FIRMA'
                                                                                            ? 'Reintentar firma'
                                                                                            : parte.estado_firma === 'REQUIERE_PASSWORD'
                                                                                            ? 'Firmar con contraseña'
                                                                                            : 'Firmar'}
                                                                                    </span>
                                                                                </DropdownMenuItem>
                                                                            )}
                                                                        </>
                                                                    )}
                                                                    <DropdownMenuItem>
                                                                        <Link
                                                                            href={`/partesemergencias/${parte.id}`}
                                                                            data={{ search: filters.search, year: filters.year }}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <FoldersIcon className="h-4 w-4" />
                                                                            <span>Archivos</span>
                                                                        </Link>
                                                                    </DropdownMenuItem>
                                                                    {['SIN_FIRMA','FIRMADO'].includes(parte.estado_firma) && (                                                                        
                                                                        <DropdownMenuItem>
                                                                            <a
                                                                                href={`/partesemergencias/${parte.id}/descargar`}
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
                            <Pagination links={partesEmergencias.links} />
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
                open={parteEmergenciaToDelete !== null}
                onOpenChange={() => setParteEmergenciaToDelete(null)}
                title="¿Está seguro?"
                description="Esta acción no se puede deshacer. El parte de emergencia será eliminado permanentemente."
                confirmText="Eliminar"
                cancelText="Cancelar"
                variant="delete"
                onConfirm={() => {
                    if (parteEmergenciaToDelete) {
                        router.delete(`/partesemergencias/${parteEmergenciaToDelete}`, {
                            preserveScroll: true,
                        });
                    }
                    setParteEmergenciaToDelete(null);
                }}
            />

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
