<?php

namespace App\Enums;

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
