<?php

namespace App\Enums;

enum RecursoNecesario: string
{
    case ESPECIAL = 'ES';
    case MEDIO = 'ME';
    case MÍNIMO = 'MI';

    public function label(): string
    {
        return match ($this) {
            self::ESPECIAL => 'Especial',
            self::MEDIO => 'Medio',
            self::MÍNIMO => 'Mínimo',
        };
    }
}