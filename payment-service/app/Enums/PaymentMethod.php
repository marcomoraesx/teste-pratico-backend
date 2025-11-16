<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'CREDIT_CARD';
    case DEBIT_CARD = 'DEBIT_CARD';
    case PIX = 'PIX';
    case MONEY = 'MONEY';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Credit card',
            self::DEBIT_CARD => 'Debit card',
            self::PIX => 'Pix',
            self::MONEY => 'Money',
        };
    }
}
