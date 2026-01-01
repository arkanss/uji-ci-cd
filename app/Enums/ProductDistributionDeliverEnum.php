<?php

namespace App\Enums;

enum ProductDistributionDeliverEnum: int 
{
    case Pending = 1;
    case InTransit = 2;
    case Delivered = 3;
    
    public function label(): string
    {
        return match ($this){
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Delivered  => 'Delivered',
        };
    }

    public function color() : string 
    {
        return match ($this) {
            self::Pending => 'blue',
            self::InTransit => 'yellow',
            self::Delivered => 'green',
        };    
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}