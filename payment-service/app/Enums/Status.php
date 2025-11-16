<?php

namespace App\Enums;

enum Status: string
{
    case PENDING = 'PENDING';
    case CANCELED = 'CANCELED';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CANCELED => 'Canceled',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }
}
