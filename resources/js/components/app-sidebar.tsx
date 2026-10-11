import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, Bolt, Settings, LifeBuoy, Users, CircleDollarSign, Package, ScrollText, BarChart3, ClipboardList, Signature, Car, Boxes } from 'lucide-react';
import AppLogo from './app-logo';

export interface MainNavItem {
    title: string;
    href: string;
    icon?: any;
    roles?: number[];
    subItems?: MainNavItem[];
}

const filterByRoles = (items: MainNavItem[], userRoles: number[]): MainNavItem[] => {
    return items
        .map(item => {
            const subItems = item.subItems
                ? filterByRoles(item.subItems, userRoles)
                : [];

            const hasAccess =
                !item.roles || item.roles.some(role => userRoles.includes(role));

            if (hasAccess || subItems.length > 0) {
                return {
                    ...item,
                    ...(subItems.length > 0 ? { subItems } : {})
                };
            }

            return null;
        })
        .filter((item): item is MainNavItem => item !== null);
};

const mainNavItems: MainNavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Prevención',
        href: '#',
        icon: Package,
        roles: [1, 3, 4],
        subItems: [
            {
                title: 'Solicitudes',
                href: '/solicitudesinspecciones',
                roles: [1, 3],
            },
            {
                title: 'Informes inspección',
                href: '/inspecciones',
                roles: [1, 4],
            },
            {
                title: 'Novedades',
                href: '/inspecciones/novedades',
                roles: [1, 3],
            },
            {
                title: 'Notificaciones',
                href: '/notificaciones',
                roles: [1, 3, 4],
            },
            {
                title: 'Informes diarios',
                href: '/informesdiarios',
                roles: [1, 4],
            },
        ]
    },
    {
        title: 'Recaudaciones',
        href: '#',
        icon: CircleDollarSign,
        roles: [1, 3, 5],
        subItems: [
            {
                title: 'Permisos',
                href: '/permisosfuncionamientos',
            }
        ]
    },
    {
        title: 'Talento Humano',
        href: '#',
        icon: Users,
        roles: [1, 7],
        subItems: [
            {
                title: 'Puestos',
                href: '/puestos',
            },
            {
                title: 'Personal',
                href: '/personales',
            }
        ]
    },
    {
        title: 'Emergencias',
        href: '#',
        icon: LifeBuoy,
        roles: [1, 6, 13],
        subItems: [
            {
                title: 'Categorias',
                href: '/categoriasemergencias',
                roles: [1, 13],
            },
            {
                title: 'Insumos',
                href: '/insumos',
                roles: [1, 13],
            },
            {
                title: 'Partes emergencias',
                href: '/partesemergencias',
                roles: [1, 6, 13],
            }
        ]
    },
    {
        title: 'Inventario',
        href: '#',
        icon: Boxes,
        roles: [1, 5, 12],
        subItems: [
            {
                title: 'Clasificación',
                href: '/clasificacionesbienes',
                roles: [5],
            },
            {
                title: 'Bienes',
                href: '/bienes',
                roles: [1, 5, 12],
            }
        ]
    },
    {
        title: 'Control vehicular',
        href: '#',
        icon: Car,
        roles: [1, 11, 13],
        subItems: [
            {
                title: 'Control combustible',
                href: '/controlescombustibles',
            }
        ]
    },
    {
        title: 'Consignas',
        href: '#',
        icon: ClipboardList,
        roles: [1, 6, 13],
        subItems: [
            {
                title: 'Turnos de guardia',
                href: '/turnosguardias',
                roles: [1, 13],
            },
            {
                title: 'Funciones',
                href: '/funcionesconsignas',
                roles: [1, 13],
            },
            {
                title: 'Consignas fijas',
                href: '/consignasfijas',
                roles: [1, 13],
            },
            {
                title: 'Relevos',
                href: '/relevosconsignas',
                roles: [1, 13],
            }
            ,
            {
                title: 'Partes diarios',
                href: '/partesconsignas',
                roles: [1, 6, 13],
            }
        ]
    },
    {
        title: 'Firma documentos',
        href: '#',
        icon: Signature,
        subItems: [
            {
                title: 'Inspecciones',
                href: '/firmas/inspecciones',
            },
            {
                title: 'Permisos',
                href: '/firmas/permisosfuncionamientos',
            },
            {
                title: 'Partes emergencia',
                href: '/firmas/partesemergencias',
            }
        ]
    },
    {
        title: 'Reportes recaudación',
        href: '#',
        icon: ScrollText,
        roles: [1, 2, 3, 5],
        subItems: [
            {
                title: 'Permisos',
                href: '/reportespermisosfuncionamientos',
            },
            {
                title: 'Avanzados',
                href: '/reportesavanzados',
            }
        ]
    },
    {
        title: 'Reportes',
        href: '#',
        icon: ScrollText,
        roles: [1, 2, 3, 5, 11, 13],
        subItems: [
            {
                title: 'Prevención',
                href: '/reportesprevenciones',
                roles: [1, 2, 3, 5],
            },
            {
                title: 'Emergencias',
                href: '/reportesemergencias',
                roles: [1, 2, 13],
            },
            {
                title: 'Control combustible',
                href: '/reportescombustibles',
                roles: [1, 2, 11],
            }
        ]
    },
    {
        title: 'Estadísticas',
        href: '#',
        icon: BarChart3,
        roles: [1, 2, 3, 5, 13],
        subItems: [
            {
                title: 'Prevención',
                href: '/estadisticasprevenciones',
                roles: [1, 2, 3, 5],
            },
            {
                title: 'Emergencias',
                href: '/estadisticasemergencias',
                roles: [1, 2, 13],
            }
        ]
    },
    {
        title: 'Opciones generales',
        href: '#',
        icon: Bolt,
        roles: [1, 3, 4],
        subItems: [
            {
                title: 'Clientes',
                href: '/clientes',
                roles: [1, 3],
            },
            {
                title: 'Tarifario',
                href: '/tarifarios',
                roles: [1, 3],
            },
            {
                title: 'Extintores',
                href: '/extintores',
                roles: [1, 3, 4],
            }
        ]
    },
    {
        title: 'Administración',
        href: '#',
        icon: Settings,
        roles: [1],
        subItems: [
            {
                title: 'Usuarios',
                href: '/users',
            },
            {
                title: 'Plantillas',
                href: '/plantillas',
            },
            {
                title: 'Institución',
                href: '/institucion/1/edit',
            },
            {
                title: 'Configuración',
                href: '/configuracion/1/edit',
            },
        ]
    }, 
];

export function AppSidebar() {
    const { auth } = usePage().props as {
        auth?: {
            roles?: number[];
        };
    };

    const roles: number[] = auth?.roles ?? [];

    const filteredNav = filterByRoles(mainNavItems, roles);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredNav} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
