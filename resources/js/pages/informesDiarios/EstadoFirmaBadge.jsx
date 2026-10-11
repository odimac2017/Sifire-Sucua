import { CheckCircle, Clock, Lock, AlertTriangle, PenOff } from "lucide-react";

export default function EstadoFirmaBadge({ informeDiario }) {
    return (
        <div className="flex items-center gap-2">
            {informeDiario.estado_firma === 'SIN_FIRMA' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">
                    <PenOff className="w-3 h-3" />
                    Sin firma
                </span>
            )}

            {informeDiario.estado_firma === 'EN_PROCESO' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">
                    <Clock className="w-3 h-3" />
                    En proceso
                </span>
            )}

            {informeDiario.estado_firma === 'PENDIENTE' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">
                    <Clock className="w-3 h-3" />
                    Pendiente
                </span>
            )}

            {informeDiario.estado_firma === 'FIRMADO' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">
                    <CheckCircle className="w-3 h-3" />
                    Firmado
                </span>
            )}

            {informeDiario.estado_firma === 'ERROR_FIRMA' && (
                <span
                    title={informeDiario.error_firma}
                    className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium cursor-help"
                >
                    <AlertTriangle className="w-3 h-3" />
                    Error
                </span>
            )}

            {informeDiario.estado_firma === 'REQUIERE_PASSWORD' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700 font-medium">
                    <Lock className="w-3 h-3" />
                    Requiere clave
                </span>
            )}

            {informeDiario.estado_firma === 'CERTIFICADO_VENCIDO' && (
                <span className="flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-medium">
                    <AlertTriangle className="w-3 h-3" />
                    Cert. vencido
                </span>
            )}
        </div>
    );
}