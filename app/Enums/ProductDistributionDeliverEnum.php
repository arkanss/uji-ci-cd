<?php

namespace App\Enums;

enum ProductDistributionDeliverEnum: int 
{
    case Completed = 1;
    case InCompleted = 2;
    
    public function label(): string
    {
        return match ($this){
            self::Completed => 'Completed',
            self::InCompleted => 'In Completed',
        };
    }

    public function color() : string 
    {
        return match ($this) {
            self::Completed => 'green',
            self::InCompleted => 'yellowp',
        };    
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}