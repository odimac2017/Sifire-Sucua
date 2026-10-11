<?php

namespace App\Enums;

enum NivelRiesgo: string
{
    case ALTO = 'AL';
    case MEDIO = 'ME';
    case BAJO = 'BA';

    public function label(): string
    {
        return match ($this) {
            self::ALTO => 'Alto',
            self::MEDIO => 'Medio',
            self::BAJO => 'Bajo',
        };
    }
}