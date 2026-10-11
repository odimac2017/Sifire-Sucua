<?php

namespace App\Enums;

enum Ubicacion: string
{
    case CANTÓN = 'CA';
    case PARROQUIA = 'PA';
    case RURAL = 'RU';

    public function label(): string
    {
        return match ($this) {
            self::CANTÓN => 'Cantón',
            self::PARROQUIA => 'Parroquia',
            self::RURAL => 'Rural',
        };
    }
}