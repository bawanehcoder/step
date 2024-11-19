<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PendingPickup = 'pending pickup';
    case PickedUp = 'picked up';
    case ReadyForDelivery = 'ready for delivery';
    case OutForDelivery = 'out for delivery';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Damaged = 'damaged';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingPickup => 'Pending Pickup',
            self::PickedUp => 'Picked Up',
            self::ReadyForDelivery => 'Ready for Delivery',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Returned => 'Returned',
            self::Damaged => 'Damaged',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PendingPickup => 'info',
            self::PickedUp => 'warning',
            self::ReadyForDelivery => 'success',
            self::OutForDelivery => 'primary',
            self::Delivered => 'success',
            self::Returned => 'danger',
            self::Damaged => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PendingPickup => 'heroicon-m-clock',
            self::PickedUp => 'heroicon-m-clipboard-document-check',
            self::ReadyForDelivery => 'heroicon-m-cube',
            self::OutForDelivery => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-check-badge',
            self::Returned => 'heroicon-m-arrow-uturn-left',
            self::Damaged => 'heroicon-m-x-circle',
        };
    }
}
