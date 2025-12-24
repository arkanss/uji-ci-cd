<?php

namespace App\Enums;

enum ChallengeTypeEnum: int
{
    case Daily = 1;
    case Weekly = 2;
    case Monthly = 3;
    case Yearly = 4;

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
