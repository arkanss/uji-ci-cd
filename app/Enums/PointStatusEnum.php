<?php

namespace App\Enums;

enum PointStatusEnum: int 
{
    case Active = 1;
    case Inactive = 2;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
