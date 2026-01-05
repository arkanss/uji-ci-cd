<?php

namespace App\Enums;

enum PurchaseOrderStatusEnum: int
{
    case Requested = 1;
    case Completed = 2;
    case Rejected = 3; 

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'yellow',
            self::Completed => 'green',
            self::Rejected => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Requested => 'clock',
            self::Completed => 'check-circle',
            self::Rejected => 'x-circle',
        };
    }
}