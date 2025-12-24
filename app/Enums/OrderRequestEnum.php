<?php

namespace App\Enums;

enum OrderRequestEnum: int
{
    case Requested = 1;
    case Verified = 2;
    case Processing = 3;
    case Processed = 4;
    case Delivering = 5;
    case Delivered = 6;
    case Completed = 7;
    case Rejected = 8;

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Verified => 'Verified',
            self::Processing => 'Processing',
            self::Processed => 'Processed',
            self::Delivering => 'Delivering',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested  => 'zinc',
            self::Verified   => 'blue',
            self::Processing => 'orange',
            self::Processed  => 'indigo',
            self::Delivering => 'blue',
            self::Delivered  => 'green',
            self::Completed  => 'green',
            self::Rejected   => 'red',
        };
    }

    public static function fromInt(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
