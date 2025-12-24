<?php

namespace App\Enums;

enum ChallengeStatusEnum: int 
{
    case Active = 1;
    case Inactive = 2;
    case Draft = 3;
    case Ended = 4;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Draft => 'Draft',
            self::Ended => 'Ended',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
