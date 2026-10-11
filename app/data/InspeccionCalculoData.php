<?php

namespace App\Data;

class InspeccionCalculoData
{
    public function __construct(
        public float $valor_inspeccion,
        public float $valor_descuento,
        public float $valor_permiso,
        public float $valor_inspecciones_anteriores,
        public float $subtotal,
        public float $valor_total
    ) {}
}