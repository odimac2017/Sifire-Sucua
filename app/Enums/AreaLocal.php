<?php

namespace App\Enums;

enum AreaLocal: string
{
    case GRANDE = 'GR';
    case MEDIA = 'ME';
    case PEQUEÑO = 'PE';
    case MÍNIMO = 'MI';

    public function label(): string
    {
        return match ($this) {
            self::GRANDE => 'Grande',
            self::MEDIA => 'Media',
            self::PEQUEÑO => 'Pequeño',
            self::MÍNIMO => 'Mínimo',
        };
    }
}