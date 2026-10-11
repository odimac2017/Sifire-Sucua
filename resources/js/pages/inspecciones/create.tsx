import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { AlertTriangle, BellRingIcon, Building2, BuildingIcon, Calendar1Icon, CarIcon, CheckCheckIcon, CheckCircle, ChevronDownIcon, Droplet, FileText, Flame, Fuel, Home, IdCard, Layers, Mail, MapPin, MinusCircle, Phone, PlusIcon, RefreshCwIcon, SaveIcon, Shield, ShieldCheck, Store, Trash2, Trees, TrendingDown, TrendingUp, TriangleAlertIcon, Undo2Icon, User2Icon, UserCog, Warehouse, XCircle, Zap } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useEffect, useState} from 'react';
import { Spinner } from '@/components/ui/spinner';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Switch } from '@/components/ui/switch';
import InputField from '@/components/form/InputField';
import { Label } from '@/components/ui/label';
import NumberInput from '@/components/form/NumberInput';
import { money } from "@/utils/format";
import TextAreaField from '@/components/form/TextAreaField';
import axios from 'axios';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { cn } from '@/lib/utils';
import InputError from '@/components/input-error';
import RiskCard from '@/components/ui/RiskCard';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

dayjs.extend(utc);
dayjs.extend(timezone);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Crear informe de inspección',
        href: '/inspecciones/create',
    },
];

interface Parroquia {
    id: number;
    nombre: string;
}

interface ProcesoPermisoTarifario {
    id: number;
    tarifario_id: number;
    valor_tarifario: number;
    valor_fijo: number;
    calc_nivel_riesgo_item: string;
    calc_nivel_riesgo_valor: number;
    calc_local_item: string;
    calc_local_valor: number;
    calc_recursos_item: string;
    calc_recursos_valor: number;
    calc_ubicacion_item: string;
    calc_ubicacion_valor: number;
    cantidad: number;
    subtotal: number;
    tarifario: {
        nombre: string;
    };
}

interface Cliente {
    id: number,
    documento: string,
    nombre: string,
    parroquia?: Parroquia;
    direccion: string,
    telefonos: string,
    email: string,    
    porcentaje_descuento: number,
}

interface ActividadEconomica {
    id: number;
    tipo_actividad: string;
    e_nombre_comercial: string;
    e_actividad_principal: string;
    parroquia?: Parroquia;
    e_direccion: string;
    v_placa: string;
    v_marca: string;
    v_tipo: string;
    v_clase: string;
    v_color: string;
}

interface Extintor { 
    numero: string;
    tipo: string;
    capacidad: number;
    estado: string;
}

interface UltimaInspeccion {
    proceso_permiso_id: number;
    numero: string;
    e_tipo_funcion: string | null;
    e_tipo_funcion_otros: string | null;
    e_materiales_almacena: string | null;
    e_rgo_estr_hormigon: boolean | null;
    e_rgo_estr_madera: boolean | null;
    e_rgo_estr_mixta: boolean | null;
    e_rgo_estr_metalica: boolean | null;
    e_rgo_estr_otros: string | null;
    e_rgo_estr_cub_metalica: boolean | null;
    e_rgo_estr_cub_madera: boolean | null;
    e_rgo_estr_cub_tumbado: boolean | null;
    e_rgo_estr_cub_fibrocemento: boolean | null;
    e_rgo_estr_cub_playwood: boolean | null;
    e_rgo_estr_cub_otros: string | null;
    e_rgo_estr_num_pisos: number | null;
    e_rgo_estr_piso_ocupa: number | null;
    e_rgo_estr_area_local: number | null;
    e_rgo_estr_surt_diesel: number | null;
    e_rgo_estr_surt_gasolina: number | null;
    e_rgo_estr_cap_diesel: number | null;
    e_rgo_estr_cap_gasolina: number | null;
    e_rgo_estr_cap_gas: number | null;
    e_rgo_estr_trat_ingnifugo: boolean | null;
    e_rgo_estr_piso_antideslisante: boolean | null;
    e_rgo_estr_piso_deslisante: boolean | null;
    e_rgo_estr_piso_otro: boolean | null;
    e_rgo_estr_norma_inen: string | null;
    e_rgo_estr_observaciones: string | null;
    e_rgo_elect_buenas: boolean | null;
    e_rgo_elect_defectuosas: boolean | null;
    e_rgo_elect_malas: boolean | null;
    e_rgo_elect_empotradas: boolean | null;
    e_rgo_elect_sobrepuestas: boolean | null;
    e_rgo_elect_volt_110: boolean | null;
    e_rgo_elect_volt_220: boolean | null;
    e_rgo_elect_volt_industrial: boolean | null;
    e_rgo_elect_protec_electrica: boolean | null;
    e_rgo_elect_transformadores: boolean | null;
    e_rgo_elect_trans_kva: number | null;
    e_rgo_elect_generadores: boolean | null;
    e_rgo_elect_electricos: boolean | null;
    e_rgo_elect_combustible: string | null;
    e_rgo_salud_epp_casco: boolean | null;
    e_rgo_salud_epp_gafas: boolean | null;
    e_rgo_salud_epp_calzado: boolean | null;
    e_rgo_salud_epp_vestimenta: boolean | null;
    e_rgo_salud_epp_guantes: boolean | null;
    e_rgo_salud_epp_orejeras: boolean | null;
    e_rgo_salud_observaciones: string | null;
    e_sis_incend_pqs: boolean | null;
    e_sis_incend_co2: boolean | null;
    e_sis_incend_h2o: boolean | null;
    e_sis_incend_capacidad: number | null;
    e_sis_incend_det_humo: boolean | null;
    e_sis_incend_lamp_emer: boolean | null;
    e_sis_incend_gabinete: boolean | null;
    e_sis_incend_sprinklers: boolean | null;
    e_sis_incend_alarm_incendio: boolean | null;
    e_sis_incend_bomb_hidraulica: boolean | null;
    e_sis_incend_siamesa: boolean | null;
    e_sis_incend_escalera_emer: boolean | null;
    e_sis_incend_puert_corta_fuego: boolean | null;
    e_sis_incend_senal_emer: boolean | null;
    e_sis_incend_puert_emer: boolean | null;
    e_sis_incend_tanq_reserv_agua: boolean | null;
    e_sis_incend_cap_tanq_reserv: boolean | null;
    v_tipo_combustible: string | null;
    v_cantidad_combustible: number | null;
    v_cantidad_cilindros: number | null;
    v_origen_transporte: string | null;
    v_destino_transporte: string | null;
    v_tipo_extintor: string | null;
    v_capacidad_extintor: number | null;
    v_triangulos_seguridad: string | null;
    v_materiales_peligrosos: string | null;
    v_control_derrames: string | null;
    v_descarga_tierra: string | null;
    v_arrestallamas: string | null;
    v_senalizacion: string | null;
    v_botiquin: string | null;
    v_luces_stop: string | null;
    v_luces_parking: string | null;
    medidas_obligatorias: string | null;
    extintores_observaciones: string | null;
    valor_permiso: number | null;
    valor_inspeccion: number | null;
    valor_descuento: number | null;
    valor_inspecciones_anteriores: number | null;
    subtotal: number | null;
    valor_total: number | null;
    estado_informe: string;
}

interface ProcesoPermiso {
    id: number;
    cliente_id: number;
    actividad_economica_id: number;
    artesano_calificado: number;
    porcentaje_descuento: number;
    valor_impuesto_predial: number;    
    cliente: Cliente;
    actividad_economica: ActividadEconomica;
    tarifarios: ProcesoPermisoTarifario[];
    ultima_inspeccion?: UltimaInspeccion;
}

interface SolicitudInspeccion {
    id: number;
    proceso_permiso_id: number;
    tipo_solicitud: string;
    proceso_permiso: ProcesoPermiso;
}

interface CalculoPermiso {
    id: number;
    calc_niv_riesg_alto: number | string;
    calc_niv_riesg_medio: number | string;
    calc_niv_riesg_bajo: number | string;
    calc_local_grande: number | string;
    calc_local_media: number | string;
    calc_local_pequeno: number | string;
    calc_local_minimo: number | string;
    calc_rec_necesarios_especial: number | string;
    calc_rec_necesarios_medio: number | string;
    calc_rec_necesarios_minimo: number | string;
    calc_ubicacion_canton: number | string;
    calc_ubicacion_parroquia: number | string;
    calc_ubicacion_rural: number | string;
}

interface Reinspeccion {
    valor_inspeccion_inicial: number;
    valor_inspeccion: number;
    subtotal: number;
    valor_total: number;
}

interface InspeccionAnterior {
    id: number;
    numero_formateado: string;
    fecha_inspeccion: string;
    nombre_personal: string;
    medidas_obligatorias: string;
    estado: string;
}

interface Props {
    solicitudInspeccion: SolicitudInspeccion;
    numero: number;
    porcentajeReinspeccion: number;
    calculoPermiso: CalculoPermiso;
    reinspeccion: Reinspeccion;
    inspeccionesAnteriores: InspeccionAnterior[];
    filters: {
        year: number | null;
        search: string;
        estado: string;
    };
}

export default function Create({solicitudInspeccion, numero, porcentajeReinspeccion, calculoPermiso, reinspeccion, inspeccionesAnteriores, filters}: Props) {

    const cliente = solicitudInspeccion.proceso_permiso?.cliente;
    const actividadEconomica = solicitudInspeccion.proceso_permiso?.actividad_economica;
    const ES_ESTABLECIMIENTO = solicitudInspeccion.proceso_permiso?.actividad_economica.tipo_actividad === "E";

    const [tarifariosLocal, setTarifariosLocal] = useState<ProcesoPermisoTarifario[]>([]);

    useEffect(() => {
        const tarifarios = solicitudInspeccion?.proceso_permiso?.tarifarios;

        if (Array.isArray(tarifarios)) {
            setTarifariosLocal(tarifarios);
        }
    }, [solicitudInspeccion?.proceso_permiso?.tarifarios]);

    const esReinspeccion = solicitudInspeccion.tipo_solicitud === 'RE';
    const ultima = solicitudInspeccion.proceso_permiso?.ultima_inspeccion;
    
    const getValue = <K extends keyof UltimaInspeccion>(
        field: K,
        defaultValue: UltimaInspeccion[K]
    ): UltimaInspeccion[K] => {
        return esReinspeccion
            ? (ultima?.[field] ?? defaultValue)
            : defaultValue;
    };

    const defaultData = {
        proceso_permiso_id: solicitudInspeccion.proceso_permiso_id,
        cliente_id: solicitudInspeccion.proceso_permiso?.cliente_id,
        actividad_economica_id: solicitudInspeccion.proceso_permiso?.actividad_economica_id,
        solicitud_inspeccion_id: solicitudInspeccion.id || 0,
        numero: numero,
        e_tipo_funcion: 'P',
        e_tipo_funcion_otros: '',
        e_materiales_almacena: '',
        e_rgo_estr_hormigon: false,
        e_rgo_estr_madera: false,
        e_rgo_estr_mixta: false,
        e_rgo_estr_metalica: false,
        e_rgo_estr_otros: '',
        e_rgo_estr_cub_metalica: false,
        e_rgo_estr_cub_madera: false,
        e_rgo_estr_cub_tumbado: false,
        e_rgo_estr_cub_fibrocemento: false,
        e_rgo_estr_cub_playwood: false,
        e_rgo_estr_cub_otros: '',
        e_rgo_estr_num_pisos: 0,
        e_rgo_estr_piso_ocupa: 0,
        e_rgo_estr_area_local: null,
        e_rgo_estr_surt_diesel: 0,
        e_rgo_estr_surt_gasolina: 0,
        e_rgo_estr_cap_diesel: 0,
        e_rgo_estr_cap_gasolina: 0,
        e_rgo_estr_cap_gas: 0,
        e_rgo_estr_trat_ingnifugo: false,
        e_rgo_estr_piso_antideslisante: false,
        e_rgo_estr_piso_deslisante: false,
        e_rgo_estr_piso_otro: false,
        e_rgo_estr_norma_inen: '',
        e_rgo_estr_observaciones: '',
        e_rgo_elect_buenas: false,
        e_rgo_elect_defectuosas: false,
        e_rgo_elect_malas: false,
        e_rgo_elect_empotradas: false,
        e_rgo_elect_sobrepuestas: false,
        e_rgo_elect_volt_110: false,
        e_rgo_elect_volt_220: false,
        e_rgo_elect_volt_industrial: false,
        e_rgo_elect_protec_electrica: false,
        e_rgo_elect_transformadores: false,
        e_rgo_elect_trans_kva: 0,
        e_rgo_elect_generadores: false,
        e_rgo_elect_electricos: false,
        e_rgo_elect_combustible: '',
        e_rgo_salud_epp_casco: false,
        e_rgo_salud_epp_gafas: false,
        e_rgo_salud_epp_calzado: false,
        e_rgo_salud_epp_vestimenta: false,
        e_rgo_salud_epp_guantes: false,
        e_rgo_salud_epp_orejeras: false,
        e_rgo_salud_observaciones: '',
        e_sis_incend_pqs: false,
        e_sis_incend_co2: false,
        e_sis_incend_h2o: false,
        e_sis_incend_capacidad: 0,
        e_sis_incend_det_humo: false,
        e_sis_incend_lamp_emer: false,
        e_sis_incend_gabinete: false,
        e_sis_incend_sprinklers: false,
        e_sis_incend_alarm_incendio: false,
        e_sis_incend_bomb_hidraulica: false,
        e_sis_incend_siamesa: false,
        e_sis_incend_escalera_emer: false,
        e_sis_incend_puert_corta_fuego: false,
        e_sis_incend_senal_emer: false,
        e_sis_incend_puert_emer: false,
        e_sis_incend_tanq_reserv_agua: false,
        e_sis_incend_cap_tanq_reserv: false,
        v_tipo_combustible: '',
        v_cantidad_combustible: 0,
        v_cantidad_cilindros: 0,
        v_origen_transporte: '',
        v_destino_transporte: '',
        v_tipo_extintor: '',
        v_capacidad_extintor: 0,
        v_triangulos_seguridad: '',
        v_materiales_peligrosos: '',
        v_control_derrames: '',
        v_descarga_tierra: '',
        v_arrestallamas: '',
        v_senalizacion: '',
        v_botiquin: '',
        v_luces_stop: '',
        v_luces_parking: '',
        medidas_obligatorias: '',
        extintores_observaciones: '',
        valor_permiso: 0,
        valor_inspeccion_inicial: esReinspeccion
            ? reinspeccion?.valor_inspeccion_inicial ?? 0
            : 0,
        valor_inspeccion: esReinspeccion
            ? reinspeccion?.valor_inspeccion ?? 0
            : 0,
        valor_descuento: 0,
        valor_inspecciones_anteriores: 0,
        subtotal: esReinspeccion
            ? reinspeccion?.subtotal ?? 0
            : 0,
        valor_total: esReinspeccion
            ? reinspeccion?.valor_total ?? 0
            : 0,
        estado_informe: '',
        extintores: [],
        search: filters?.search ?? '',
        year: filters.year ?? new Date().getFullYear(),
        estado: filters?.estado ?? '',
    }

    // CAMPOS QUE NO SE CONSIDERAN DE LA INSPECCIÓN ANTERIOR
    const limpiarInspeccion = (data?: UltimaInspeccion): Partial<UltimaInspeccion> => {
        if (!data) return {};

        const {
            id,
            solicitud_inspeccion_id,
            fecha_inspeccion,
            valor_permiso,
            valor_inspeccion,
            valor_descuento,  
            valor_inspecciones_anteriores,
            subtotal,
            valor_total,
            estado_informe,
            estado_firma,
            ruta_pdf,
            ruta_pdf_firmado,
            correo_enviado_at,
            correo_destinatario,
            correo_estado,
            correo_error,
            codigo_verificacion,
            created_at,
            updated_at,
            ...rest
        } = data as any;

        return rest;
    };

    const mergeInspeccion = (
        base: typeof defaultData,
        ultima?: Partial<UltimaInspeccion>
    ) => {
        if (!ultima) return base;

        const limpia = limpiarInspeccion(ultima as UltimaInspeccion);

        const result: any = { ...base };

        Object.keys(limpia).forEach((key) => {
            const value = (limpia as any)[key];

            if (value !== null && value !== undefined) {
                result[key] = value;
            }
        });

        return result;
    };

    // MEZCLAR DATOS DE LA INSPECCIÓN ANTERIOR CON LA NUEVA 
    const initialData = {
        ...mergeInspeccion(
            defaultData,
            esReinspeccion ? ultima : undefined
        ),
        proceso_permiso: {
            ...solicitudInspeccion.proceso_permiso,
            tarifarios: solicitudInspeccion.proceso_permiso?.tarifarios ?? [],
        },
        extintores: [] as Extintor[]
    };

    type FormData = typeof initialData;

    const { data, setData, post, processing, errors, transform } = useForm<FormData>(initialData);

    const esGLP = data.v_tipo_combustible === "GLP";
    const esCombustible = data.v_tipo_combustible === "GASOLINA" || data.v_tipo_combustible === "DIÉSEL";

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        transform((current) => ({
            ...current,
            proceso_permiso: {
                ...current.proceso_permiso,
                tarifarios: tarifariosLocal,
            },
        }));

        post("/inspecciones");
    };

    const [extintorForm, setExtintorForm] = useState({
        cantidad: 1,
        numero: '',
        tipo: '',
        capacidad: 0,
        estado: ''
    });

    const [loadingNumero, setLoadingNumero] = useState(false);

    const [formErrors, setFormErrors] = useState({
        tipo: '',
        capacidad: '',
        estado: ''
    });

    const agregarExtintor = async () => {
        try {
            let newErrors = {
                tipo: '',
                capacidad: '',
                estado: ''
            };

            if (!extintorForm.tipo) {
                newErrors.tipo = "Seleccione el tipo";
            }

            if (!extintorForm.capacidad) {
                newErrors.capacidad = "Seleccione la capacidad";
            }

            if (!extintorForm.estado) {
                newErrors.estado = "Seleccione el estado";
            }

            if (newErrors.tipo || newErrors.capacidad || newErrors.estado) {
                setFormErrors(newErrors);
                return;
            }

            setLoadingNumero(true);

            const res = await axios.get('/extintores/generar');

            let numeroBase = parseInt(res.data.numero);

            const nuevosExtintores = [];

            for (let i = 0; i < extintorForm.cantidad; i++) {
                const numeroFinal = numeroBase + data.extintores.length + i;

                const numeroFormateado = numeroFinal
                    .toString()
                    .padStart(7, '0');

                nuevosExtintores.push({
                    numero: numeroFormateado,
                    tipo: extintorForm.tipo,
                    capacidad: extintorForm.capacidad,
                    estado: extintorForm.estado
                });
            }

            // 🔥 agregar todos de una sola vez
            setData('extintores', [
                ...data.extintores,
                ...nuevosExtintores
            ]);

            // limpiar
            setExtintorForm({
                cantidad: 1,
                numero: '',
                tipo: '',
                capacidad: 0,
                estado: '',            
            });

            setFormErrors({
                tipo: '',
                capacidad: '',
                estado: ''
            });

        } catch (error) {
            console.error(error);
        } finally {
            setLoadingNumero(false);
        }
    };

    const eliminarExtintor = (index: number) => {
        const nuevos = data.extintores.filter((_: Extintor, i: number) => i !== index);
        setData('extintores', nuevos);
    };

    const n = (v: any) => Number(v ?? 0);

    const cambiarNivelRiesgo = (index: number, value: "AL" | "ME" | "BA") => {
        setTarifariosLocal((prev) =>
            prev.map((t, i) => {
                if (i !== index) return t;

                const updated = {
                    ...t,
                    calc_nivel_riesgo_item: value,
                };

                const calc_nivel_riesgo_valor = calcularNivelRiesgo(updated);

                const subtotal = calcularSubtotalTarifa({
                    ...updated,
                    calc_nivel_riesgo_valor,
                });

                return {
                    ...updated,
                    calc_nivel_riesgo_valor,
                    subtotal,
                };
            })
        );
    };

    const calcularNivelRiesgo = (tarifa: ProcesoPermisoTarifario) => {
        const item = tarifa.calc_nivel_riesgo_item;

        if (!item) return 0;
        const factor =
            item === "AL"
                ? n(calculoPermiso?.calc_niv_riesg_alto) ?? 0
                : item === "ME"
                ? n(calculoPermiso?.calc_niv_riesg_medio) ?? 0
                : n(calculoPermiso?.calc_niv_riesg_bajo) ?? 0;

        return n(tarifa.valor_tarifario) * n(factor);
    };

    const cambiarAreaLocal = (index: number, value: "GR" | "ME" | "PE" | "MI") => {
        setTarifariosLocal((prev) =>
            prev.map((t, i) => {
                if (i !== index) return t;

                const updated = {
                    ...t,
                    calc_local_item: value,
                };

                const calc_local_valor = calcularAreaLocal(updated);

                const subtotal = calcularSubtotalTarifa({
                    ...updated,
                    calc_local_valor,
                });

                return {
                    ...updated,
                    calc_local_valor,
                    subtotal,
                };
            })
        );
    };

    const calcularAreaLocal = (tarifa: ProcesoPermisoTarifario) => {
        const item = tarifa.calc_local_item;

        if (!item) return 0;
        const factor =
            item === "GR"
                ? n(calculoPermiso?.calc_local_grande) ?? 0
                : item === "ME"
                ? n(calculoPermiso?.calc_local_media) ?? 0
                : item === "PE"
                ? n(calculoPermiso?.calc_local_pequeno) ?? 0
                : n(calculoPermiso?.calc_local_minimo) ?? 0;

        return n(tarifa.valor_tarifario) * n(factor);
    };

    const cambiarRecurso = (index: number, value: "ES" | "ME" | "MI") => {   
        setTarifariosLocal((prev) =>
            prev.map((t, i) => {
                if (i !== index) return t;

                const updated = {
                    ...t,
                    calc_recursos_item: value,
                };

                const calc_recursos_valor = calcularRecurso(updated);

                const subtotal = calcularSubtotalTarifa({
                    ...updated,
                    calc_recursos_valor,
                });

                return {
                    ...updated,
                    calc_recursos_valor,
                    subtotal,
                };
            })
        );
    };

    const calcularRecurso = (tarifa: ProcesoPermisoTarifario) => {
        const item = tarifa.calc_recursos_item;

        if (!item) return 0;
        const factor =
            item === "ES"
                ? n(calculoPermiso?.calc_rec_necesarios_especial) ?? 0
                : item === "ME"
                ? n(calculoPermiso?.calc_rec_necesarios_medio) ?? 0
                : n(calculoPermiso?.calc_rec_necesarios_minimo) ?? 0;

        return n(tarifa.valor_tarifario) * n(factor);
    };

    const cambiarUbicacion = (index: number, value: "CA" | "PA" | "RU") => {
        setTarifariosLocal((prev) =>
            prev.map((t, i) => {
                if (i !== index) return t;

                const updated = {
                    ...t,
                    calc_ubicacion_item: value,
                };

                const calc_ubicacion_valor = calcularUbicacion(updated);

                const subtotal = calcularSubtotalTarifa({
                    ...updated,
                    calc_ubicacion_valor,
                });

                return {
                    ...updated,
                    calc_ubicacion_valor,
                    subtotal,
                };
            })
        );
    };

    const calcularUbicacion = (tarifa: ProcesoPermisoTarifario) => {
        const item = tarifa.calc_ubicacion_item;

        if (!item) return 0;
        const factor =
            item === "CA"
                ? n(calculoPermiso?.calc_ubicacion_canton) ?? 0
                : item === "PA"
                ? n(calculoPermiso?.calc_ubicacion_parroquia) ?? 0
                : n(calculoPermiso?.calc_ubicacion_rural) ?? 0;

        return n(tarifa.valor_tarifario) * n(factor);
    };

    const calcularSubtotalTarifa = (tarifa: ProcesoPermisoTarifario) => {
        const base =
            n(calcularNivelRiesgo(tarifa)) +
            n(calcularAreaLocal(tarifa)) +
            n(calcularRecurso(tarifa)) +
            n(calcularUbicacion(tarifa));

        if (tarifa.valor_fijo === 0) {
            return base;
        }

        const cantidad = n(tarifa.cantidad);
        const valor = n(tarifa.valor_tarifario);

        return n(valor) * n(cantidad);
    };

    useEffect(() => {
        if (solicitudInspeccion.tipo_solicitud === "RE") return;

        const total = tarifariosLocal.reduce((acc, tarifa) => {
            return acc + n(tarifa.subtotal ?? 0);
        }, 0);

        axios.post("/inspecciones/calcular", {
            proceso_permiso_id: solicitudInspeccion.proceso_permiso?.id,
            valor_inspeccion: n(total),
        }).then((response) => {
            const r = response.data;

            setData({
                ...data,
                valor_inspeccion: total,
                valor_descuento: r.valor_descuento ?? 0,
                valor_permiso: r.valor_permiso ?? 0,
                valor_inspecciones_anteriores: r.valor_inspecciones_anteriores ?? 0,
                subtotal: r.subtotal ?? 0,
                valor_total: r.valor_total ?? 0,
            });
        });
    }, [tarifariosLocal, solicitudInspeccion.tipo_solicitud, solicitudInspeccion.proceso_permiso?.id]);

    const textoAprobado = `La actividad económica ${solicitudInspeccion.proceso_permiso.actividad_economica.e_nombre_comercial} perteneciente al/la cliente ${solicitudInspeccion.proceso_permiso.cliente.nombre} con CC/RUC # ${solicitudInspeccion.proceso_permiso.cliente.documento} cumple con las medidas de seguridad para su funcionamiento.`;

    useEffect(() => {
        if (data.estado_informe === 'APROBADO') {
            setData('medidas_obligatorias', textoAprobado);
        } else {
            setData('medidas_obligatorias', '');
        }
    }, [data.estado_informe]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear informe de inspección" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                    <div className="flex justify-between items-start border-b pb-2 mb-3">
                        <div className="flex items-start gap-3 w-full">
                            <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                <User2Icon className="w-8 h-8 text-gray-700" />
                            </div>
                            <div className="w-full">
                                <h1 className="text-lg font-semibold leading-tight">{cliente.nombre}</h1>
                                <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                    <IdCard className="w-4 h-4" />
                                    <span>Documento:</span>
                                    <span className="font-medium text-foreground">{cliente.documento}</span>
                                </div>
                            </div>
                        </div>    
                        <div className="md:col-span-2 flex gap-2 justify-end">                            
                            <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                                <Link 
                                    href={`/inspecciones`}
                                    data={{ search: data.search, year: data.year, estado: data.estado }}
                                >
                                    <Undo2Icon /> Regresar
                                </Link>
                            </Button>                             
                        </div>
                    </div>
                    <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                        <Home className="w-4 h-4" />
                        <span>Dirección:</span>
                        <span className="font-medium text-foreground">{cliente.direccion}</span>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-muted-foreground text-sm mt-2">
                        <div className="flex items-start gap-2">
                            <MapPin className="w-4 h-4" />
                            <span>Parroquia:</span>
                            <span className="font-medium text-foreground">{cliente.parroquia?.nombre}</span>
                        </div>
                        <div className="flex items-start gap-2">
                            <Phone className="w-4 h-4" />
                            <span>Teléfonos:</span>
                            <span className="font-medium text-foreground">{cliente.telefonos}</span>
                        </div>
                        <div className="flex items-start gap-2">
                            <Mail className="w-4 h-4" />
                            <span>Email:</span>
                            <span className="font-medium text-foreground">{cliente.email}</span>
                        </div>
                    </div>
                </div>
                <div className="border rounded-md bg-background shadow-sm p-4 text-[13px]">
                    <div className="flex justify-between items-start border-b pb-2 mb-3">
                        <div>
                            {ES_ESTABLECIMIENTO ? (
                                <>  
                                    <div className="flex items-start gap-3 w-full">
                                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                            <BuildingIcon className="w-8 h-8 text-gray-700" />
                                        </div>
                                        <div className="w-full">
                                            <h1 className="text-lg font-semibold leading-tight">{actividadEconomica.e_nombre_comercial}</h1>
                                            <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                                {actividadEconomica.e_actividad_principal}
                                            </div>
                                        </div>
                                    </div>
                                </>
                            ) : (
                                <>  
                                    <div className="flex items-start gap-3 w-full">
                                        <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                            <CarIcon className="w-8 h-8 text-gray-700" />
                                        </div>
                                        <div className="w-full">
                                            <h1 className="text-lg font-semibold leading-tight">{actividadEconomica.v_placa}</h1>
                                            <div className="flex items-center gap-2 text-muted-foreground text-sm mt-1">
                                                {actividadEconomica.v_marca} {actividadEconomica.v_tipo} {actividadEconomica.v_clase} {actividadEconomica.v_color}
                                            </div>
                                        </div>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-muted-foreground text-sm mt-2">
                        <div className="flex items-start gap-2 md:col-span-2">
                            <Home className="w-4 h-4" />
                            <span>Dirección:</span>
                            <span className="font-medium text-foreground">{actividadEconomica.e_direccion}</span>
                        </div>                    
                        <div className="flex items-start gap-2 md:col-span-1">
                            <MapPin className="w-4 h-4" />
                            <span>Parroquia:</span>
                            <span className="font-medium text-foreground">{actividadEconomica.parroquia?.nombre}</span>
                        </div>
                    </div>
                </div>
                <Tabs defaultValue="actual" className="w-full">
                    <TabsList variant="line">
                        <TabsTrigger value="actual">
                            Inspección actual
                        </TabsTrigger>
                        <TabsTrigger
                            value="anteriores"
                            className="text-orange-600 data-[state=active]:text-orange-700"
                        > 
                            Inspecciones anteriores
                            <Badge className="bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200">
                                {inspeccionesAnteriores && inspeccionesAnteriores.length}
                            </Badge>
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="actual">
                        <div className="relative border rounded-md bg-background shadow-sm p-4 text-[13px] mt-3">
                            <div className="absolute top-3 right-4 bg-primary text-primary-foreground px-3 py-1 rounded-md shadow-sm">
                                <span className="text-xs opacity-80 mr-1">Informe de inspección</span>
                                <span className="font-bold tracking-wider">#{numero}-TEMP</span>
                            </div>
                            <div className="flex items-start gap-3 pb-2">
                                <div className="flex-shrink-0 bg-gray-200 p-2 rounded-full">
                                    <Calendar1Icon className="w-8 h-8 text-gray-700" />
                                </div>
                                <div>
                                    <h1 className="text-lg font-semibold leading-tight">Fecha de inspección</h1>
                                    <p className="text-sm text-muted-foreground">
                                        {new Date().toLocaleString('es-EC', {
                                            dateStyle: 'long',
                                            timeStyle: 'short'
                                        }).toUpperCase()}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <form onSubmit={handleSubmit} className='space-y-4'>
                            {ES_ESTABLECIMIENTO && (
                                <>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Tipo de función
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <RadioGroup
                                                value={data.e_tipo_funcion}
                                                onValueChange={(value) => {
                                                    setData("e_tipo_funcion", value);

                                                    if (value === "O") {
                                                        setData("e_tipo_funcion_otros", "");
                                                    }
                                                }}
                                                className="grid md:grid-cols-3 gap-4"
                                            >
                                                <label
                                                    htmlFor="propietario"
                                                    className={`flex items-center gap-3 border rounded-xl p-4 cursor-pointer transition-all duration-200
                                                        ${data.e_tipo_funcion === "P"
                                                            ? "border-blue-500 bg-blue-50 dark:bg-blue-900/20"
                                                            : "hover:bg-muted"
                                                        }
                                                    `}
                                                >
                                                    <RadioGroupItem value="P" id="propietario" />
                                                    <Home className="w-5 h-5 text-blue-500" />                                   
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">Propietario</span>
                                                        <span className="text-xs text-muted-foreground">
                                                            Dueño de la actividad económica
                                                        </span>
                                                    </div>
                                                </label>
                                                <label
                                                    htmlFor="arrendatario"
                                                    className={`flex items-center gap-3  border rounded-xl p-4 cursor-pointer transition-all duration-200
                                                        ${data.e_tipo_funcion === "A"
                                                            ? "border-orange-500 bg-orange-50 dark:bg-orange-900/20"
                                                            : "hover:bg-muted"
                                                        }
                                                    `}
                                                >
                                                    <RadioGroupItem value="A" id="arrendatario" />
                                                    <FileText className="w-5 h-5 text-orange-500" />
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">Arrendatario</span>
                                                        <span className="text-xs text-muted-foreground">
                                                            Ocupa mediante contrato
                                                        </span>
                                                    </div>
                                                </label>
                                                <label
                                                    htmlFor="otro"
                                                    className={`flex items-center gap-3 border rounded-xl p-4 cursor-pointer transition-all duration-200
                                                    ${
                                                        data.e_tipo_funcion === "O"
                                                            ? "border-purple-500 bg-purple-50 dark:bg-purple-900/20"
                                                            : "hover:bg-muted"
                                                    }`}
                                                >
                                                    <RadioGroupItem value="O" id="otro" />
                                                    <UserCog className="w-5 h-5 text-purple-500" />
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">Otro</span>
                                                        <span className="text-xs text-muted-foreground">
                                                            Otra modalidad
                                                        </span>
                                                    </div>
                                                </label>
                                            </RadioGroup>
                                            {data.e_tipo_funcion === "O" && (
                                                <div className="mt-4">
                                                    <InputField
                                                        label="Describa la otra modalidad"
                                                        name="e_tipo_funcion_otros"
                                                        value={data.e_tipo_funcion_otros ?? ""}
                                                        onChange={e => setData('e_tipo_funcion_otros', e.target.value)}
                                                        error={errors.e_tipo_funcion_otros}
                                                        placeholder="Especifique"
                                                    />
                                                </div>
                                            )}
                                            <div className="mt-4">
                                                <InputField
                                                    label="Materiales que almacena"
                                                    name="e_materiales_almacena"
                                                    value={data.e_materiales_almacena ?? ""}
                                                    onChange={e => setData('e_materiales_almacena', e.target.value)}
                                                    error={errors.e_materiales_almacena}
                                                    placeholder="Especifique"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Riesgos estructural
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Tipo de estructura
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_hormigon"
                                                    checked={data.e_rgo_estr_hormigon}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_hormigon', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_hormigon">Hormigón Armado</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_madera"
                                                    checked={data.e_rgo_estr_madera}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_madera', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_madera">Madera</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_mixta"
                                                    checked={data.e_rgo_estr_mixta}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_mixta', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_mixta">Mixta</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_metalica"
                                                    checked={data.e_rgo_estr_metalica}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_metalica', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_metalica">Estructura metálica</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Otros</Label>
                                                <InputField
                                                    name="e_rgo_estr_otros"
                                                    value={data.e_rgo_estr_otros ?? ""}
                                                    onChange={e => setData('e_rgo_estr_otros', e.target.value)}
                                                    error={errors.e_rgo_estr_otros}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Tipo de cubierta
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_cub_metalica"
                                                    checked={data.e_rgo_estr_cub_metalica}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_cub_metalica', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_cub_metalica">Cubierta metálica</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_cub_madera"
                                                    checked={data.e_rgo_estr_cub_madera}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_cub_madera', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_cub_madera">Cubierta de madera</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_cub_tumbado"
                                                    checked={data.e_rgo_estr_cub_tumbado}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_cub_tumbado', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_cub_tumbado">Tumbado</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_cub_fibrocemento"
                                                    checked={data.e_rgo_estr_cub_fibrocemento}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_cub_fibrocemento', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_cub_fibrocemento">Fibrocemento</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_cub_playwood"
                                                    checked={data.e_rgo_estr_cub_playwood}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_cub_playwood', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_cub_playwood">Playwood</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Otros</Label>
                                                <InputField
                                                    name="e_rgo_estr_cub_otros"
                                                    value={data.e_rgo_estr_cub_otros ?? ""}
                                                    onChange={e => setData('e_rgo_estr_cub_otros', e.target.value)}
                                                    error={errors.e_rgo_estr_cub_otros}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Características del local
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap"># de pisos</Label>                                
                                                <NumberInput                                    
                                                    name="e_rgo_estr_num_pisos"
                                                    value={data.e_rgo_estr_num_pisos}
                                                    onChange={(value) => setData('e_rgo_estr_num_pisos', value)}
                                                    min={0}
                                                    error={errors.e_rgo_estr_num_pisos}
                                                />
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Piso que ocupa</Label>
                                                <NumberInput                                    
                                                    name="e_rgo_estr_piso_ocupa"
                                                    value={data.e_rgo_estr_piso_ocupa}
                                                    onChange={(value) => setData('e_rgo_estr_piso_ocupa', value)}
                                                    min={0}
                                                    error={errors.e_rgo_estr_piso_ocupa}
                                                />                                
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Área del local</Label>
                                                <InputField
                                                    name="e_rgo_estr_area_local"
                                                    value={data.e_rgo_estr_area_local ?? ""}
                                                    onChange={e => setData('e_rgo_estr_area_local', e.target.value)}
                                                    error={errors.e_rgo_estr_area_local}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Tipo de combustible
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <RiskCard
                                                title="Diésel"
                                                icon={Fuel}
                                                color="blue"
                                                active={data.e_rgo_estr_surt_diesel > 0 || data.e_rgo_estr_cap_diesel > 0}
                                                onToggle={() => {
                                                    const activo =
                                                        data.e_rgo_estr_surt_diesel > 0 || data.e_rgo_estr_cap_diesel > 0;

                                                    if (!activo) {
                                                        setData('e_rgo_estr_surt_diesel', 1);
                                                    } else {
                                                        setData('e_rgo_estr_surt_diesel', 0);
                                                        setData('e_rgo_estr_cap_diesel', 0);
                                                    }
                                                }}
                                            >
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <NumberInput
                                                        label="# de surtidores"
                                                        name="e_rgo_estr_surt_diesel"
                                                        value={data.e_rgo_estr_surt_diesel}
                                                        onChange={(v) => setData('e_rgo_estr_surt_diesel', v)}
                                                        min={0}
                                                    />
                                                    <NumberInput
                                                        label="Capacidad (Gls)"
                                                        name="e_rgo_estr_cap_diesel"
                                                        value={data.e_rgo_estr_cap_diesel}
                                                        onChange={(v) => setData('e_rgo_estr_cap_diesel', v)}
                                                        min={0}
                                                    />
                                                </div>
                                            </RiskCard>
                                            <RiskCard
                                                title="Gasolina"
                                                icon={Fuel}
                                                color="orange"
                                                active={data.e_rgo_estr_surt_gasolina > 0 || data.e_rgo_estr_cap_gasolina > 0}
                                                onToggle={() => {
                                                    const activo =
                                                        data.e_rgo_estr_surt_gasolina > 0 || data.e_rgo_estr_cap_gasolina > 0;

                                                    if (!activo) {
                                                        setData('e_rgo_estr_surt_gasolina', 1);
                                                    } else {
                                                        setData('e_rgo_estr_surt_gasolina', 0);
                                                        setData('e_rgo_estr_cap_gasolina', 0);
                                                    }
                                                }}
                                            >
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <NumberInput
                                                        label="# de surtidores"
                                                        name="e_rgo_estr_surt_gasolina"
                                                        value={data.e_rgo_estr_surt_gasolina}
                                                        onChange={(v) => setData('e_rgo_estr_surt_gasolina', v)}
                                                        min={0}
                                                    />
                                                    <NumberInput
                                                        label="Capacidad (Gls)"
                                                        name="e_rgo_estr_cat_gasolina"
                                                        value={data.e_rgo_estr_cap_gasolina}
                                                        onChange={(v) => setData('e_rgo_estr_cap_gasolina', v)}
                                                        min={0}
                                                    />
                                                </div>
                                            </RiskCard>
                                            <RiskCard
                                                title="Gas"
                                                icon={Flame}
                                                color="purple"
                                                active={data.e_rgo_estr_cap_gas > 0}
                                                onToggle={() => {
                                                    if (data.e_rgo_estr_cap_gas === 0) {
                                                        setData('e_rgo_estr_cap_gas', 1);
                                                    } else {
                                                        setData('e_rgo_estr_cap_gas', 0);
                                                    }
                                                }}
                                            >
                                                <NumberInput
                                                    label="Capacidad"
                                                    name="e_rgo_estr_cap_gas"
                                                    value={data.e_rgo_estr_cap_gas}
                                                    onChange={(v) => setData('e_rgo_estr_cap_gas', v)}
                                                    min={0}
                                                    
                                                />
                                            </RiskCard>                            
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Tipo de piso
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_trat_ingnifugo"
                                                    checked={data.e_rgo_estr_trat_ingnifugo}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_trat_ingnifugo', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_trat_ingnifugo">Tratamiento ingnifugo (RF 120)</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_piso_antideslisante"
                                                    checked={data.e_rgo_estr_piso_antideslisante}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_piso_antideslisante', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_piso_antideslisante">Piso antideslisante</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_piso_deslisante"
                                                    checked={data.e_rgo_estr_piso_deslisante}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_piso_deslisante', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_piso_deslisante">Piso deslisante</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_estr_piso_otro"
                                                    checked={data.e_rgo_estr_piso_otro}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_estr_piso_otro', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_estr_piso_otro">Otro piso</Label>
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Cumplimiento normativo
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Cumple Norma INEN</Label>
                                                <InputField
                                                    name="e_rgo_estr_norma_inen"
                                                    value={data.e_rgo_estr_norma_inen ?? ""}
                                                    onChange={e => setData('e_rgo_estr_norma_inen', e.target.value)}
                                                    error={errors.e_rgo_estr_norma_inen}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Observaciones</Label>
                                                <InputField
                                                    name="e_rgo_estr_observaciones"
                                                    value={data.e_rgo_estr_observaciones ?? ""}
                                                    onChange={e => setData('e_rgo_estr_observaciones', e.target.value)}
                                                    error={errors.e_rgo_estr_observaciones}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Riesgos eléctricos
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Instalaciones eléctricas
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_buenas"
                                                    checked={data.e_rgo_elect_buenas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_buenas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_buenas">Buenas</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_defectuosas"
                                                    checked={data.e_rgo_elect_defectuosas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_defectuosas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_defectuosas">Defectuosas</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_malas"
                                                    checked={data.e_rgo_elect_malas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_malas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_malas">Malas</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_empotradas"
                                                    checked={data.e_rgo_elect_empotradas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_empotradas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_empotradas">Empotradas</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_sobrepuestas"
                                                    checked={data.e_rgo_elect_sobrepuestas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_sobrepuestas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_sobrepuestas">Sobrepuestas</Label>  
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Voltaje de las instalaciones eléctricas
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_volt_110"
                                                    checked={data.e_rgo_elect_volt_110}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_volt_110', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_volt_110">110V</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_volt_220"
                                                    checked={data.e_rgo_elect_volt_220}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_volt_220', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_volt_220">220V</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_volt_industrial"
                                                    checked={data.e_rgo_elect_volt_industrial}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_volt_industrial', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_volt_industrial">Voltaje industrial</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_protec_electrica"
                                                    checked={data.e_rgo_elect_protec_electrica}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_protec_electrica', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_protec_electrica">Caja de protección eléctrica</Label>  
                                            </div>
                                            <RiskCard
                                                title="Transformadores"
                                                icon={Zap}
                                                color="blue"
                                                active={data.e_rgo_elect_transformadores}
                                                onToggle={() => {
                                                    const activo = data.e_rgo_elect_transformadores;

                                                    if (!activo) {
                                                        setData('e_rgo_elect_transformadores', true);

                                                        if (!data.e_rgo_elect_trans_kva || data.e_rgo_elect_trans_kva < 1) {
                                                            setData('e_rgo_elect_trans_kva', 1);
                                                        }
                                                    } else {
                                                        setData('e_rgo_elect_transformadores', false);
                                                        setData('e_rgo_elect_trans_kva', 0);
                                                    }
                                                }}
                                            >
                                                <NumberInput
                                                    label="Capacidad del Transformador (KVA)"
                                                    name="e_rgo_elect_trans_kva"
                                                    value={data.e_rgo_elect_trans_kva}
                                                    onChange={(v) => setData('e_rgo_elect_trans_kva', v)}
                                                    min={0}
                                                />
                                            </RiskCard>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Energía de respaldo
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_generadores"
                                                    checked={data.e_rgo_elect_generadores}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_generadores', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_generadores">Generadores</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_elect_electricos"
                                                    checked={data.e_rgo_elect_electricos}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_elect_electricos', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_elect_electricos">Eléctricos</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Combustible</Label>
                                                <InputField
                                                    name="e_rgo_elect_combustible"
                                                    value={data.e_rgo_elect_combustible ?? ""}
                                                    onChange={e => setData('e_rgo_elect_combustible', e.target.value)}
                                                    error={errors.e_rgo_elect_combustible}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Riesgos a la salud (Equipo de protección personal)
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_casco"
                                                    checked={data.e_rgo_salud_epp_casco}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_casco', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_casco">Casco</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_gafas"
                                                    checked={data.e_rgo_salud_epp_gafas}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_gafas', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_gafas">Gafas</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_calzado"
                                                    checked={data.e_rgo_salud_epp_calzado}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_calzado', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_calzado">Calzado</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_vestimenta"
                                                    checked={data.e_rgo_salud_epp_vestimenta}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_vestimenta', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_vestimenta">Vestimenta</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_guantes"
                                                    checked={data.e_rgo_salud_epp_guantes}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_guantes', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_guantes">Guantes</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_rgo_salud_epp_orejeras"
                                                    checked={data.e_rgo_salud_epp_orejeras}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_rgo_salud_epp_orejeras', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_rgo_salud_epp_orejeras">Orejeras</Label>  
                                            </div>
                                            <div className="col-span-2 space-y-2">
                                                <InputField
                                                    label="Observaciones"
                                                    name="e_rgo_salud_observaciones"
                                                    value={data.e_rgo_salud_observaciones ?? ""}
                                                    onChange={e => setData('e_rgo_salud_observaciones', e.target.value)}
                                                    error={errors.e_rgo_salud_observaciones}
                                                    placeholder="Específique"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Sistema de defensa contra incendios
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Extintores
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_pqs"
                                                    checked={data.e_sis_incend_pqs}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_pqs', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_pqs">Extintor PQS</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_co2"
                                                    checked={data.e_sis_incend_co2}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_co2', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_co2">Extintor CO2</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_h2o"
                                                    checked={data.e_sis_incend_h2o}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_h2o', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_h2o">Extintor H2O</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label className="whitespace-nowrap">Capacidad (Lbs)</Label>                                
                                                <NumberInput                                    
                                                    name="e_sis_incend_capacidad"
                                                    value={data.e_sis_incend_capacidad}
                                                    onChange={(value) => setData('e_sis_incend_capacidad', value)}
                                                    min={0}
                                                    error={errors.e_sis_incend_capacidad}
                                                />
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    Equipos y sistemas de protección contra incendios
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_det_humo"
                                                    checked={data.e_sis_incend_det_humo}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_det_humo', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_det_humo">Detector de humo</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_lamp_emer"
                                                    checked={data.e_sis_incend_lamp_emer}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_lamp_emer', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_lamp_emer">Lámpara de emergencia</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_gabinete"
                                                    checked={data.e_sis_incend_gabinete}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_gabinete', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_gabinete">Gabinete</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_sprinklers"
                                                    checked={data.e_sis_incend_sprinklers}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_sprinklers', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_sprinklers">Sprinklers</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_alarm_incendio"
                                                    checked={data.e_sis_incend_alarm_incendio}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_alarm_incendio', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_alarm_incendio">Alarma de incendio</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_bomb_hidraulica"
                                                    checked={data.e_sis_incend_bomb_hidraulica}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_bomb_hidraulica', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_bomb_hidraulica">Bomba hidráulica</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_siamesa"
                                                    checked={data.e_sis_incend_siamesa}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_siamesa', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_siamesa">Siamesa</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_escalera_emer"
                                                    checked={data.e_sis_incend_escalera_emer}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_escalera_emer', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_escalera_emer">Escalera de emergencia</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_puert_corta_fuego"
                                                    checked={data.e_sis_incend_puert_corta_fuego}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_puert_corta_fuego', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_puert_corta_fuego">Puertas corta fuego</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_senal_emer"
                                                    checked={data.e_sis_incend_senal_emer}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_senal_emer', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_senal_emer">Señalizacion de emergencia</Label>  
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Switch
                                                    id="e_sis_incend_puert_emer"
                                                    checked={data.e_sis_incend_puert_emer}
                                                    onCheckedChange={(checked) =>
                                                        setData('e_sis_incend_puert_emer', checked)
                                                    }
                                                />
                                                <Label htmlFor="e_sis_incend_puert_emer">Puertas de emergencia</Label>  
                                            </div>
                                            <div className="col-span-2 flex items-center gap-2 text-muted-foreground my-3">
                                                <Layers className="w-4 h-4" />
                                                <span className="text-sm font-semibold">
                                                    
                                                </span>
                                                <div className="flex-1 h-px bg-border ml-2"></div>
                                            </div>
                                            <RiskCard
                                                title="Tanque de reserva de agua"
                                                icon={Droplet}
                                                color="blue"
                                                active={data.e_sis_incend_tanq_reserv_agua}
                                                onToggle={() => {
                                                    const activo = data.e_sis_incend_tanq_reserv_agua;

                                                    if (!activo) {
                                                        setData('e_sis_incend_tanq_reserv_agua', true);

                                                        if (!data.e_sis_incend_cap_tanq_reserv || data.e_sis_incend_cap_tanq_reserv < 1) {
                                                            setData('e_sis_incend_cap_tanq_reserv', 1);
                                                        }
                                                    } else {
                                                        setData('e_sis_incend_tanq_reserv_agua', false);
                                                        setData('e_sis_incend_cap_tanq_reserv', 0);
                                                    }
                                                }}
                                            >
                                                <NumberInput
                                                    label="Capacidad (Gls)"
                                                    name="e_sis_incend_cap_tanq_reserv"
                                                    value={data.e_sis_incend_cap_tanq_reserv}
                                                    onChange={(v) => setData('e_sis_incend_cap_tanq_reserv', v)}
                                                    min={0}
                                                />
                                            </RiskCard>                           
                                        </CardContent>
                                    </Card>                            
                                </>
                            )}
                            {!ES_ESTABLECIMIENTO && (
                                <>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Combustible y transporte
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">
                                            <div className="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div className="space-y-2 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Tipo de combustible</Label>
                                                        <div className="relative">
                                                            <select
                                                                value={data.v_tipo_combustible}
                                                                onChange={(e) => {
                                                                    const tipo = e.target.value;

                                                                    setData({
                                                                        ...data,
                                                                        v_tipo_combustible: tipo,
                                                                        v_cantidad_combustible: tipo === "GLP" ? 0 : data.v_cantidad_combustible,
                                                                        v_cantidad_cilindros: tipo === "GLP" ? data.v_cantidad_cilindros : 0,
                                                                    });
                                                                }}
                                                                className={cn(
                                                                    "border-input flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs",
                                                                    errors.v_tipo_combustible && "border-destructive"
                                                                )}
                                                            >
                                                                <option value="">Seleccione</option>
                                                                <option value="GASOLINA">GASOLINA</option>
                                                                <option value="DIÉSEL">DIÉSEL</option>
                                                                <option value="GLP">GLP</option>
                                                            </select>
                                                            <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50"/>
                                                        </div>
                                                    </div>
                                                    <InputError message={errors.v_tipo_combustible} />
                                                </div>
                                                <div className="space-y-2 w-full">
                                                    <NumberInput
                                                        label='Cantidad (Gls)'
                                                        name="v_cantidad_combustible"
                                                        value={data.v_cantidad_combustible}
                                                        onChange={(value) => setData("v_cantidad_combustible", value)}
                                                        min={0}
                                                        error={errors.v_cantidad_combustible}
                                                        disabled={!esCombustible} 
                                                    />
                                                </div>
                                                <div className="space-y-2 w-full">
                                                    <NumberInput
                                                        label='Cilindros'
                                                        name="v_cantidad_cilindros"
                                                        value={data.v_cantidad_cilindros}
                                                        onChange={(value) => setData("v_cantidad_cilindros", value)}
                                                        min={0}
                                                        error={errors.v_cantidad_cilindros}
                                                        disabled={!esGLP}
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <InputField
                                                    label="Origen"
                                                    name="v_origen_transporte"
                                                    value={data.v_origen_transporte ?? ""}
                                                    onChange={e => setData('v_origen_transporte', e.target.value)}
                                                    error={errors.v_origen_transporte}
                                                    placeholder="Especifique"
                                                />
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <InputField
                                                    label="Destino"
                                                    name="v_destino_transporte"
                                                    value={data.v_destino_transporte ?? ""}
                                                    onChange={e => setData('v_destino_transporte', e.target.value)}
                                                    error={errors.v_destino_transporte}
                                                    placeholder="Especifique"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Card className="rounded-xl shadow-sm">
                                        <CardHeader>
                                            <CardTitle className="text-sm font-semibold leading-none">
                                                Normas de seguridad
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="grid grid-cols-2 gap-4">        
                                            <div className="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div className="space-y-2 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Tipo de extintor</Label>
                                                        <div className="relative">
                                                            <select
                                                                value={data.v_tipo_extintor}
                                                                onChange={(e) => setData("v_tipo_extintor", e.target.value)}
                                                                className={cn(
                                                                    "border-input flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs",
                                                                    errors.v_tipo_extintor && "border-destructive"
                                                                )}
                                                            >
                                                                <option value="">Seleccione</option>
                                                                <option value="PQS">PQS</option>
                                                                <option value="CO2">CO2</option>
                                                                <option value="H2O">H2O</option>
                                                            </select>
                                                            <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50"/>
                                                        </div>
                                                    </div>
                                                    <InputError message={errors.v_tipo_extintor} />
                                                </div>
                                                <div className="space-y-2 w-full">
                                                    <NumberInput
                                                        label='Capacidad (Lbs)'
                                                        name="v_capacidad_extintor"
                                                        value={data.v_capacidad_extintor}
                                                        onChange={(value) => setData("v_capacidad_extintor", value)}
                                                        min={0}
                                                        error={errors.v_capacidad_extintor}
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Triángulos de seguridad</Label>
                                                        <RadioGroup
                                                            value={data.v_triangulos_seguridad ?? ""}
                                                            onValueChange={(value) => setData("v_triangulos_seguridad", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_triangulos_seguridad === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_triangulos_seguridad} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Guía Materiales Peligrosos</Label>
                                                        <RadioGroup
                                                            value={data.v_materiales_peligrosos ?? ""}
                                                            onValueChange={(value) => setData("v_materiales_peligrosos", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_materiales_peligrosos === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_materiales_peligrosos} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Kit control derrames</Label>
                                                        <RadioGroup
                                                            value={data.v_control_derrames ?? ""}
                                                            onValueChange={(value) => setData("v_control_derrames", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_control_derrames === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_control_derrames} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Descarga a tierra</Label>
                                                        <RadioGroup
                                                            value={data.v_descarga_tierra ?? ""}
                                                            onValueChange={(value) => setData("v_descarga_tierra", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_descarga_tierra === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_descarga_tierra} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Arrestallamas</Label>
                                                        <RadioGroup
                                                            value={data.v_arrestallamas ?? ""}
                                                            onValueChange={(value) => setData("v_arrestallamas", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_arrestallamas === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_arrestallamas} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Señalización</Label>
                                                        <RadioGroup
                                                            value={data.v_senalizacion ?? ""}
                                                            onValueChange={(value) => setData("v_senalizacion", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_senalizacion === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_senalizacion} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Botiquín</Label>
                                                        <RadioGroup
                                                            value={data.v_botiquin ?? ""}
                                                            onValueChange={(value) => setData("v_botiquin", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_botiquin === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_botiquin} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Luces de stop</Label>
                                                        <RadioGroup
                                                            value={data.v_luces_stop ?? ""}
                                                            onValueChange={(value) => setData("v_luces_stop", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_luces_stop === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_luces_stop} />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 w-full">
                                                <div className="space-y-3 w-full">
                                                    <div className="grid w-full gap-1">
                                                        <Label className="mb-2">Luces de parking</Label>
                                                        <RadioGroup
                                                            value={data.v_luces_parking ?? ""}
                                                            onValueChange={(value) => setData("v_luces_parking", value)}
                                                            className="grid grid-cols-3 gap-3"    
                                                        >
                                                            {[
                                                                {
                                                                    value: "NO_APLICA",
                                                                    label: "No aplica",
                                                                    icon: MinusCircle,
                                                                    active: "bg-purple-50 border-purple-300 text-purple-700",
                                                                    inactive: "hover:bg-purple-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-purple-600",
                                                                },
                                                                {
                                                                    value: "SI",
                                                                    label: "Sí",
                                                                    icon: CheckCircle,
                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-green-600",
                                                                },
                                                                {
                                                                    value: "NO",
                                                                    label: "No",
                                                                    icon: XCircle,
                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                    iconColor: "text-red-600",
                                                                },
                                                            ].map((opt) => {
                                                                const Icon = opt.icon;
                                                                const active = data.v_luces_parking === opt.value;

                                                                return (
                                                                    <label
                                                                        key={opt.value}
                                                                        className={`
                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                            ${active ? opt.active : opt.inactive}
                                                                        `}
                                                                    >
                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                        <Icon
                                                                            className={`h-4 w-4 ${
                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                            }`}
                                                                        />
                                                                        <span className="font-medium">{opt.label}</span>
                                                                    </label>
                                                                );
                                                            })}
                                                        </RadioGroup>
                                                        <InputError message={errors.v_luces_parking} />
                                                    </div>
                                                </div>
                                            </div>    
                                        </CardContent>
                                    </Card>
                                </>
                            )}
                            <Card className="rounded-xl shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between">
                                    <CardTitle className="text-sm font-semibold">
                                        Extintores
                                    </CardTitle>                            
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
                                        <NumberInput                                    
                                            name="cantidad"
                                            value={extintorForm.cantidad}
                                            onChange={(value) =>
                                                setExtintorForm({
                                                    ...extintorForm,
                                                    cantidad: value
                                                })
                                            }
                                            min={1}                                    
                                        />
                                        <div className="grid w-full gap-1 relative">
                                            <div className="relative">
                                                <select
                                                    name="tipo"
                                                    value={extintorForm.tipo}
                                                    onChange={(e) =>
                                                        setExtintorForm({
                                                            ...extintorForm,
                                                            tipo: e.target.value
                                                        })
                                                    }
                                                    className={cn(
                                                        "border-input placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                                                        formErrors.tipo && "border-destructive"
                                                    )}
                                                >
                                                    <option value={0}>Seleccione</option>
                                                    <option value='CO2'>CO2</option>
                                                    <option value='PQS'>PQS</option>
                                                    <option value='PURPURA K'>PURPURA K</option>
                                                </select>
                                                <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50" />
                                            </div>
                                            <InputError message={formErrors.tipo} />
                                        </div>
                                        <div className="grid w-full gap-1 relative">
                                            <div className="relative">
                                                <select
                                                    name="capacidad"
                                                    value={extintorForm.capacidad}
                                                    onChange={(e) =>
                                                        setExtintorForm({
                                                            ...extintorForm,
                                                            capacidad: n(e.target.value)
                                                        })
                                                    }
                                                    className={cn(
                                                        "border-input placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                                                        formErrors.capacidad && "border-destructive"
                                                    )}
                                                >
                                                    <option value={0}>Seleccione</option>
                                                    <option value='5'>5 LBS</option>
                                                    <option value='10'>10 LBS</option>
                                                    <option value='15'>15 LBS</option>
                                                    <option value='20'>20 LBS</option>
                                                    <option value='20'>60 LBS</option>
                                                    <option value='20'>80 LBS</option>
                                                    <option value='20'>100 LBS</option>
                                                </select>
                                                <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50" />
                                            </div>
                                            <InputError message={formErrors.capacidad} />
                                        </div>
                                        <div className="grid w-full gap-1 relative">
                                            <div className="relative">
                                                <select
                                                    name="estado"
                                                    value={extintorForm.estado}
                                                    onChange={(e) =>
                                                        setExtintorForm({
                                                            ...extintorForm,
                                                            estado: e.target.value
                                                        })
                                                    }
                                                    className={cn(
                                                        "border-input placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                                                        formErrors.estado && "border-destructive"
                                                    )}
                                                >
                                                    <option value={0}>Seleccione</option>
                                                    <option value='BUENO'>BUENO</option>
                                                    <option value='REGULAR'>REGULAR</option>
                                                    <option value='MALO'>MALO</option>
                                                </select>
                                                <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50" />
                                            </div>
                                            <InputError message={formErrors.estado} />
                                        </div>
                                        <button
                                            type="button"
                                            onClick={agregarExtintor}
                                            disabled={loadingNumero}
                                            className="self-start w-full flex items-center justify-center gap-2 bg-green-600 text-white px-3 py-2 rounded-lg text-sm disabled:opacity-50 cursor-pointer"
                                        >
                                            <>
                                            {loadingNumero ? (
                                                <Spinner className="w-4 h-4 animate-spin" />
                                            ) : (
                                                <PlusIcon className="w-4 h-4" />
                                            )}
                                            <span>
                                                Agregar extintor
                                            </span>
                                        </>
                                        </button>
                                    </div>                            
                                    {data.extintores.length ? (
                                        <div className="border rounded-xl shadow-sm overflow-hidden">
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead>
                                                        <tr className="bg-muted">
                                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                                            <th className="py-2 px-4 text-left border-b">Tipo</th>
                                                            <th className="py-2 px-4 text-left border-b">Capacidad</th>
                                                            <th className="py-2 px-4 text-left border-b">Estado</th>
                                                            <th className="py-2 px-4 text-left border-b"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {data.extintores.map((ext: Extintor, index: number) => (
                                                            <tr key={index} className="border-t">
                                                                <td className="py-2 px-4 text-left border-b">{ext.numero}</td>
                                                                <td className="py-2 px-4 text-left border-b">{ext.tipo}</td>
                                                                <td className="py-2 px-4 text-left border-b">{ext.capacidad} LBS</td>
                                                                <td className="py-2 px-4 text-left border-b">{ext.estado}</td>
                                                                <td className="py-2 px-4 text-left border-b">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => eliminarExtintor(index)}
                                                                        className="text-red-500 hover:text-red-700 transition cursor-pointer"
                                                                    >
                                                                        <Trash2 className="w-4 h-4" />
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="flex justify-center my-2">
                                            <Alert className="w-full sm:w-1/2 lg:w-1/2 bg-destructive/10 text-destructive border-none">
                                                <TriangleAlertIcon className="h-4 w-4" />
                                                <AlertTitle>Este informe no cuenta con extintores registrados</AlertTitle>
                                                <AlertDescription className="text-destructive/80">
                                                    Por favor agregue un nuevo extintor.
                                                </AlertDescription>
                                            </Alert>
                                        </div>
                                    )}  
                                    <div className="col-span-2 space-y-2">
                                        <InputField
                                            label="Observaciones"
                                            name="extintores_observaciones"
                                            value={data.extintores_observaciones ?? ""}
                                            onChange={e => setData('extintores_observaciones', e.target.value)}
                                            error={errors.extintores_observaciones}
                                            placeholder="Específique"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="rounded-xl shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between">
                                    <CardTitle className="text-sm font-semibold">
                                        Cálculo para obtener el valor del permiso de funcionamiento
                                    </CardTitle>                            
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {tarifariosLocal.map((tarifa, index) => (
                                        <div
                                            key={tarifa.id ?? index}
                                            className="rounded-xl border bg-card text-card-foreground shadow-sm"
                                        >
                                            <div className="p-4 space-y-4">        
                                                <div className="flex items-start justify-between">
                                                    <div className="space-y-0.5">
                                                        <p className="text-sm font-medium">
                                                            {tarifa.tarifario.nombre}
                                                        </p>
                                                    </div>
                                                    <div className="text-right">
                                                        <Badge className="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-50">
                                                            ${n(tarifa.valor_tarifario).toFixed(2)}
                                                        </Badge>
                                                    </div>
                                                </div>
                                                {!esReinspeccion && (
                                                    <>
                                                        <div className="h-px bg-border" />
                                                        {tarifa.valor_fijo === 0 && (
                                                            <>                                                
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <div className="space-y-2">
                                                                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                                            Nivel de riesgo
                                                                        </p>
                                                                        <RadioGroup
                                                                            value={tarifariosLocal[index]?.calc_nivel_riesgo_item ?? ""}
                                                                            onValueChange={(value: "AL" | "ME" | "BA") =>
                                                                                cambiarNivelRiesgo(index, value)
                                                                            }
                                                                            className="flex flex-wrap gap-3"
                                                                        >
                                                                            {[
                                                                                {
                                                                                    value: "AL",
                                                                                    label: "Alto",
                                                                                    icon: AlertTriangle,
                                                                                    active:
                                                                                        "bg-red-50 border-red-300 text-red-700",
                                                                                    inactive:
                                                                                        "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-red-600",
                                                                                },
                                                                                {
                                                                                    value: "ME",
                                                                                    label: "Medio",
                                                                                    icon: Shield,
                                                                                    active:
                                                                                        "bg-yellow-50 border-yellow-300 text-yellow-700",
                                                                                    inactive:
                                                                                        "hover:bg-yellow-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-yellow-600",
                                                                                },
                                                                                {
                                                                                    value: "BA",
                                                                                    label: "Bajo",
                                                                                    icon: ShieldCheck,
                                                                                    active:
                                                                                        "bg-green-50 border-green-300 text-green-700",
                                                                                    inactive:
                                                                                        "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-green-600",
                                                                                },
                                                                            ].map((opt) => {
                                                                                const Icon = opt.icon;

                                                                                const active = tarifariosLocal[index]?.calc_nivel_riesgo_item === opt.value;

                                                                                return (
                                                                                    <label
                                                                                        key={opt.value}
                                                                                        className={`
                                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                                            ${active ? opt.active : opt.inactive}
                                                                                        `}
                                                                                    >
                                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                                        <Icon
                                                                                            className={`h-4 w-4 ${
                                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                                            }`}
                                                                                        />
                                                                                        <span className="font-medium">
                                                                                            {opt.label}
                                                                                        </span>
                                                                                    </label>
                                                                                );
                                                                            })}
                                                                        </RadioGroup>
                                                                    </div>
                                                                    <div className="md:text-right">                
                                                                        <p className="text-md font-bold text-primary tabular-nums">
                                                                            ${calcularNivelRiesgo(tarifa).toFixed(2)}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <div className="space-y-2">
                                                                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                                            Área del local
                                                                        </p>
                                                                        <RadioGroup
                                                                            value={tarifariosLocal[index]?.calc_local_item ?? ""}
                                                                            onValueChange={(value: "GR" | "ME" | "PE" | "MI") =>
                                                                                cambiarAreaLocal(index, value)
                                                                            }
                                                                            className="flex flex-wrap gap-3"
                                                                        >
                                                                            {[
                                                                                {
                                                                                    value: "GR",
                                                                                    label: "Grande",
                                                                                    icon: Building2,
                                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-red-600",
                                                                                },
                                                                                {
                                                                                    value: "ME",
                                                                                    label: "Media",
                                                                                    icon: Warehouse,
                                                                                    active: "bg-yellow-50 border-yellow-300 text-yellow-700",
                                                                                    inactive: "hover:bg-yellow-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-yellow-600",
                                                                                },
                                                                                {
                                                                                    value: "PE",
                                                                                    label: "Pequeño",
                                                                                    icon: Home,
                                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-green-600",
                                                                                },
                                                                                {
                                                                                    value: "MI",
                                                                                    label: "Mínimo",
                                                                                    icon: Store,
                                                                                    active: "bg-sky-50 border-sky-300 text-sky-700",
                                                                                    inactive: "hover:bg-sky-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-sky-600",
                                                                                },
                                                                            ].map((opt) => {
                                                                                const Icon = opt.icon;

                                                                                const active = tarifariosLocal[index]?.calc_local_item === opt.value;

                                                                                return (
                                                                                    <label
                                                                                        key={opt.value}
                                                                                        className={`
                                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                                            ${active ? opt.active : opt.inactive}
                                                                                        `}
                                                                                    >
                                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                                        <Icon
                                                                                            className={`h-4 w-4 ${
                                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                                            }`}
                                                                                        />
                                                                                        <span className="font-medium">
                                                                                            {opt.label}
                                                                                        </span>
                                                                                    </label>
                                                                                );
                                                                            })}
                                                                        </RadioGroup>
                                                                    </div>
                                                                    <div className="md:text-right">                
                                                                        <p className="text-md font-bold text-primary tabular-nums">
                                                                            ${calcularAreaLocal (tarifa).toFixed(2)}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <div className="space-y-2">
                                                                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                                            Recursos necesarios
                                                                        </p>
                                                                        <RadioGroup
                                                                            value={tarifariosLocal[index]?.calc_recursos_item ?? ""}
                                                                            onValueChange={(value: "ES" | "ME" | "MI") =>
                                                                                cambiarRecurso(index, value)
                                                                            }
                                                                            className="flex flex-wrap gap-3"
                                                                        >
                                                                            {[
                                                                                {
                                                                                    value: "ES",
                                                                                    label: "Especial",
                                                                                    icon: AlertTriangle,
                                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-red-600",
                                                                                },
                                                                                {
                                                                                    value: "ME",
                                                                                    label: "Medio",
                                                                                    icon: TrendingUp,
                                                                                    active: "bg-yellow-50 border-yellow-300 text-yellow-700",
                                                                                    inactive: "hover:bg-yellow-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-yellow-600",
                                                                                },
                                                                                {
                                                                                    value: "MI",
                                                                                    label: "Mínimo",
                                                                                    icon: TrendingDown,
                                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-green-600",
                                                                                }
                                                                            ].map((opt) => {
                                                                                const Icon = opt.icon;

                                                                                const active = tarifariosLocal[index]?.calc_recursos_item === opt.value;

                                                                                return (
                                                                                    <label
                                                                                        key={opt.value}
                                                                                        className={`
                                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                                            ${active ? opt.active : opt.inactive}
                                                                                        `}
                                                                                    >
                                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                                        <Icon
                                                                                            className={`h-4 w-4 ${
                                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                                            }`}
                                                                                        />
                                                                                        <span className="font-medium">
                                                                                            {opt.label}
                                                                                        </span>
                                                                                    </label>
                                                                                );
                                                                            })}
                                                                        </RadioGroup>
                                                                    </div>
                                                                    <div className="md:text-right">                
                                                                        <p className="text-md font-bold text-primary tabular-nums">
                                                                            ${calcularRecurso (tarifa).toFixed(2)}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <div className="space-y-2">
                                                                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                                            Ubicación
                                                                        </p>
                                                                        <RadioGroup
                                                                            value={tarifariosLocal[index]?.calc_ubicacion_item ?? ""}
                                                                            onValueChange={(value: "CA" | "PA" | "RU") =>
                                                                                cambiarUbicacion(index, value)
                                                                            }
                                                                            className="flex flex-wrap gap-3"
                                                                        >
                                                                            {[
                                                                                {
                                                                                    value: "CA",
                                                                                    label: "Cantón",
                                                                                    icon: Building2,
                                                                                    active: "bg-red-50 border-red-300 text-red-700",
                                                                                    inactive: "hover:bg-red-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-red-600",
                                                                                },
                                                                                {
                                                                                    value: "PA",
                                                                                    label: "Parroquia",
                                                                                    icon: MapPin,
                                                                                    active: "bg-yellow-50 border-yellow-300 text-yellow-700",
                                                                                    inactive: "hover:bg-yellow-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-yellow-600",
                                                                                },
                                                                                {
                                                                                    value: "RU",
                                                                                    label: "Rural",
                                                                                    icon: Trees,
                                                                                    active: "bg-green-50 border-green-300 text-green-700",
                                                                                    inactive: "hover:bg-green-50/30 border-muted text-muted-foreground",
                                                                                    iconColor: "text-green-600",
                                                                                }
                                                                            ].map((opt) => {
                                                                                const Icon = opt.icon;

                                                                                const active =
                                                                                    tarifariosLocal[index]?.calc_ubicacion_item === opt.value;

                                                                                return (
                                                                                    <label
                                                                                        key={opt.value}
                                                                                        className={`
                                                                                            flex items-center gap-2 px-4 py-2 rounded-full border text-sm cursor-pointer transition
                                                                                            ${active ? opt.active : opt.inactive}
                                                                                        `}
                                                                                    >
                                                                                        <RadioGroupItem value={opt.value} className="sr-only" />
                                                                                        <Icon
                                                                                            className={`h-4 w-4 ${
                                                                                                active ? opt.iconColor : "text-muted-foreground"
                                                                                            }`}
                                                                                        />
                                                                                        <span className="font-medium">
                                                                                            {opt.label}
                                                                                        </span>
                                                                                    </label>
                                                                                );
                                                                            })}
                                                                        </RadioGroup>
                                                                    </div>
                                                                    <div className="md:text-right">                
                                                                        <p className="text-md font-bold text-primary tabular-nums">
                                                                            ${calcularUbicacion (tarifa).toFixed(2)}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div className="h-px bg-border" />
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <span className="text-sm font-medium text-muted-foreground">
                                                                        Subtotal
                                                                    </span>

                                                                    <div className="text-right">
                                                                        <Badge className="text-md bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50">
                                                                            ${(calcularSubtotalTarifa(tarifa) ?? 0).toFixed(2)}
                                                                        </Badge>
                                                                    </div>
                                                                </div>
                                                            </>
                                                        )}
                                                        {tarifa.valor_fijo === 1 && (      
                                                            <>                                      
                                                                <div className="space-y-1">                            
                                                                    <NumberInput   
                                                                        label='Cantidad'       
                                                                        name="cantidad"
                                                                        value={tarifa.cantidad}
                                                                        onChange={(value: number) => {
                                                                            setTarifariosLocal(prev =>
                                                                                prev.map((t, i) => {
                                                                                    if (i !== index) return t;

                                                                                    const updated = {
                                                                                        ...t,
                                                                                        cantidad: value,
                                                                                    };

                                                                                    updated.subtotal = calcularSubtotalTarifa(updated);

                                                                                    return updated;
                                                                                })
                                                                            );
                                                                        }}
                                                                        min={0}
                                                                        error={errors.cantidad}
                                                                    />
                                                                </div>  
                                                                <p className="text-xs text-muted-foreground mt-1">
                                                                    Áreas / vehiculos / espacios / cabinas / computadoras / máquinas / plazas / cilindros / otros
                                                                </p>
                                                                <div className="h-px bg-border" />
                                                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                    <span className="text-sm font-medium text-muted-foreground">
                                                                        Subtotal
                                                                    </span>

                                                                    <div className="text-right">
                                                                        <Badge className="text-md bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50">
                                                                            $ {(calcularSubtotalTarifa(tarifa) ?? 0).toFixed(2)}
                                                                        </Badge>
                                                                    </div>
                                                                </div>                                        
                                                            </>
                                                        )}
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                    <Card className="rounded-xl shadow-sm">
                                        <CardContent className="space-y-3">
                                            {esReinspeccion && (
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-600">
                                                        Valor de la inspección técnica inicial
                                                    </span>
                                                    <div className="flex items-center gap-1">
                                                        <span className="font-semibold text-blue-700 dark:text-blue-400 tabular-nums">
                                                            {money(data.valor_inspeccion_inicial)}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                            USD
                                                        </span>
                                                    </div>
                                                </div>
                                            )}
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">
                                                    {esReinspeccion
                                                        ? `Valor de la reinspección técnica (${porcentajeReinspeccion}%)`
                                                        : "Valor de la inspección técnica"
                                                    }
                                                </span>
                                                <div className="flex items-center gap-1">
                                                    <span 
                                                    className={`font-semibold tabular-nums ${
                                                            esReinspeccion
                                                                ? "text-orange-600 dark:text-orange-400"
                                                                : "text-blue-700 dark:text-blue-400"
                                                        }`}
                                                    >
                                                        {esReinspeccion
                                                            ? "+"
                                                            : ""
                                                        }
                                                        {money(data.valor_inspeccion)}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                        USD
                                                    </span>
                                                </div>
                                            </div>
                                            {!esReinspeccion && (
                                                <>
                                                    <div className="flex justify-between text-sm">
                                                        <span className="text-gray-600">
                                                            Descuento ({solicitudInspeccion.proceso_permiso.cliente.porcentaje_descuento}%)
                                                        </span>
                                                        <div className="flex items-center gap-1">
                                                            <span className="font-semibold text-green-600 dark:text-green-400 tabular-nums">
                                                                -{money(data.valor_descuento)}
                                                            </span>
                                                            <span className="text-xs text-muted-foreground ml-1 dark:text-gray-400">
                                                                USD
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="flex justify-between text-sm">
                                                        <span className="text-gray-600">
                                                            Valor del permiso
                                                        </span>
                                                        <div className="flex items-center gap-1">
                                                            <span className="font-semibold text-orange-600 dark:text-orange-400 tabular-nums">
                                                                +{money(data.valor_permiso)}
                                                            </span>
                                                            <span className="text-xs text-muted-foreground ml-1">
                                                                USD
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="flex justify-between text-sm">
                                                        <span className="text-gray-600">
                                                            Valor de inspecciones anteriores sin permisos
                                                        </span>
                                                        <div className="flex items-center gap-1">
                                                            <span className="font-semibold text-red-600 dark:text-red-400 tabular-nums">
                                                                +{money(data.valor_inspecciones_anteriores)}
                                                            </span>
                                                            <span className="text-xs text-muted-foreground ml-1">
                                                                USD
                                                            </span>
                                                        </div>
                                                    </div>
                                                </>
                                            )}
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">
                                                    Subtotal
                                                </span>
                                                <div className="flex items-center gap-1">
                                                    <span className="font-semibold text-blue-600 dark:text-blue-400 tabular-nums">
                                                        {money(data.subtotal)}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground ml-1">
                                                        USD
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="h-px bg-border" />
                                            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                                        <span>Valor a pagar</span>
                                                        {solicitudInspeccion.proceso_permiso?.artesano_calificado === 1 && (
                                                            <span className="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">
                                                                Artesano calificado
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-xs text-muted-foreground">
                                                        Este es el valor a pagar por el cliente si el informe es aprobado
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <Badge className="px-3 py-2 text-md bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50">
                                                        <span className="font-semibold tabular-nums">
                                                            {money(data.valor_total)}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground ml-1">
                                                            USD
                                                        </span>
                                                    </Badge>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </CardContent>
                            </Card>
                            <Card className="rounded-xl shadow-sm">
                                <CardContent className="space-y-3">
                                    <div className="col-span-2">
                                        <TextAreaField
                                            label="Medidas obligatorias que debe cumplir"
                                            name="medidas_obligatorias"
                                            value={data.medidas_obligatorias}
                                            maxLength={1000}
                                            placeholder="Especifique"
                                            error={errors.medidas_obligatorias}
                                            onChange={value => setData('medidas_obligatorias', value)}
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="rounded-xl shadow-sm">
                                <CardHeader>
                                    <CardTitle className="text-sm font-semibold leading-none">
                                        Criterio técnico del inspector
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <RadioGroup
                                        value={data.estado_informe}
                                        onValueChange={(value) => setData("estado_informe", value)}
                                        className={`grid grid-cols-1 md:${esReinspeccion ? "grid-cols-3" : "grid-cols-2"} gap-4`}
                                    >
                                        <label
                                            htmlFor="aprobado"
                                            className={`flex items-center gap-3 border rounded-xl p-4 cursor-pointer transition-all duration-200
                                                ${data.estado_informe === "APROBADO"
                                                    ? "border-green-500 bg-green-50 dark:bg-green-900/20"
                                                    : "hover:bg-muted"
                                                }
                                            `}
                                        >
                                            <RadioGroupItem value="APROBADO" id="aprobado" />
                                            <CheckCheckIcon className="w-5 h-5 text-green-500" /> 
                                            <div className="flex flex-col">
                                                <span className="font-medium">Aprobado</span>
                                                <span className="text-xs text-muted-foreground">
                                                    Informe favorable
                                                </span>
                                            </div>
                                        </label>
                                        <label
                                            htmlFor="reinspeccion"
                                            className={`flex items-center gap-3 border rounded-xl p-4 cursor-pointer transition-all duration-200
                                                ${data.estado_informe === "REINSPECCION"
                                                    ? "border-orange-500 bg-orange-50 dark:bg-orange-900/20"
                                                    : "hover:bg-muted"
                                                }
                                            `}
                                        >
                                            <RadioGroupItem value="REINSPECCION" id="reinspeccion" />
                                            <RefreshCwIcon className="w-5 h-5 text-orange-500" />
                                            <div className="flex flex-col">
                                                <span className="font-medium">Para reinspección</span>
                                                <span className="text-xs text-muted-foreground">
                                                    Informe con novedades encontradas durante la inspección
                                                </span>
                                            </div>
                                        </label>
                                        {esReinspeccion && (
                                            <label
                                                htmlFor="notificacion"
                                                className={`flex items-center gap-3 border rounded-xl p-4 cursor-pointer transition-all
                                                ${data.estado_informe === "NOTIFICACION"
                                                    ? "border-red-500 bg-red-50 dark:bg-red-900/20"
                                                    : "hover:bg-muted"
                                                }`}
                                            >
                                                <RadioGroupItem value="NOTIFICACION" id="notificacion" />
                                                <BellRingIcon className="w-5 h-5 text-red-500" />

                                                <div className="flex flex-col">
                                                    <span className="font-medium">Para notificación</span>
                                                    <span className="text-xs text-muted-foreground">
                                                        Inicio del proceso de notificación
                                                    </span>
                                                </div>
                                            </label>
                                        )}
                                    </RadioGroup>
                                    {errors.estado_informe && (
                                        <p className="text-sm text-red-500 mt-2">
                                            {errors.estado_informe}
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                            <div className="flex gap-2">                           
                                <Button disabled={processing} type='submit' size="sm" className='cursor-pointer'>
                                    {!processing && <SaveIcon />} {processing && <Spinner />} Crear informe
                                </Button>
                                <Button asChild size="sm" className="bg-slate-500 hover:bg-slate-700">
                                    <Link 
                                        href={`/inspecciones`}
                                        data={{ search: data.search, year: data.year, estado: data.estado }}
                                    >
                                        <Undo2Icon /> Regresar
                                    </Link>
                                </Button>
                            </div> 
                        </form>  
                    </TabsContent>
                    <TabsContent value="anteriores">
                        <div className="border rounded-xl shadow-sm overflow-hidden mt-3">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-muted">
                                            <th className="py-2 px-4 text-left border-b">Número</th>
                                            <th className="py-2 px-4 text-left border-b">Fecha</th>
                                            <th className="py-2 px-4 text-left border-b">Inspector</th> 
                                            <th className="py-2 px-4 text-left border-b">Estado</th>
                                            <th className="py-2 px-4 text-left border-b">Medidas obligatorias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {inspeccionesAnteriores.map((inspeccion) => (
                                            <tr
                                                className="odd:bg-muted/50 hover:bg-muted/30"
                                                key={inspeccion.id}
                                            >
                                                <td className="py-2 px-4 border-b"><span className='font-medium'>{inspeccion.numero_formateado}</span></td>
                                                <td className="py-2 px-4 border-b">{dayjs(inspeccion.fecha_inspeccion).format('YYYY-MM-DD')}</td>
                                                <td className="py-2 px-4 border-b">{inspeccion.nombre_personal}</td>
                                                <td className="py-2 px-4 border-b">
                                                    <Badge
                                                        className={`border-none focus-visible:outline-none
                                                            ${inspeccion.estado === "APROBADO"
                                                                ? "bg-green-600/10 text-green-600 dark:bg-green-400/10 dark:text-green-400"
                                                                : inspeccion.estado === "REINSPECCION"
                                                                ? "bg-orange-600/10 text-orange-600 dark:bg-orange-400/10 dark:text-orange-400"
                                                                : "bg-blue-600/10 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400"
                                                            }
                                                        `}
                                                    >
                                                        {inspeccion.estado}
                                                    </Badge>
                                                </td>
                                                <td className="py-2 px-4 border-b">{inspeccion.medidas_obligatorias}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );    
}
