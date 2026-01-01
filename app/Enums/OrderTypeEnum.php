<?php

namespace App\Enums;

enum OrderTypeEnum: int
{
    case Consignment = 1;
    case Buy = 2;

    public function label(): string
    {
        return match ($this) {
            self::Consignment => 'Consignment',
            self::Buy => 'Buy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Consignment => 'amber',
            self::Buy => 'emerald',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Consignment => 'archive-box',
            self::Buy => 'shopping-cart',
        };
    }
}
