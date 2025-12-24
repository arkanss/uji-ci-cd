<?php

namespace App\Enums;

enum ChallengeTriggerTypeEnum: int
{
    case WaterIntake = 1;
    case Transaction = 2;
    case Steps = 3;
    case Login = 4;
    case Referral = 5;
    case Purchase = 6;

    public function label(): string
    {
        return match ($this) {
            self::WaterIntake => 'Water Intake',
            self::Transaction => 'Transaction',
            self::Steps => 'Steps',
            self::Login => 'Login',
            self::Referral => 'Referral',
            self::Purchase => 'Purchase',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
