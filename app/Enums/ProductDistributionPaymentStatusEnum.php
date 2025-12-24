<?php

namespace App\Enums;

enum ProductDistributionPaymentStatusEnum: int 
{
    case Pending = 1;
    case WaitingVerification = 2;
    case Paid = 3;
    case Rejected = 4;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::WaitingVerification => 'Waiting Verification',
            self::Paid => 'Paid',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending             => 'zinc',   
            self::WaitingVerification => 'amber',  
            self::Paid                => 'green',  
            self::Rejected            => 'red',    
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
