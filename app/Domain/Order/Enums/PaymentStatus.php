<?php

namespace App\Domain\Order\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Processing = 'processing';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Не оплачен',
            self::Processing => 'Процессинг',
            self::Paid => 'Оплачен',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}

