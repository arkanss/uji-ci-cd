<?php

namespace App\Enums;

enum DPItemRequestsEnum: int
{
    case Requested = 1;
    case Processed = 2;
    case Opened = 3;
    case Closed = 4;

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Processed => 'Processed',
            self::Opened => 'Opened',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Processed => 'yellow',
            self::Opened => 'green',
            self::Closed => 'red',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}