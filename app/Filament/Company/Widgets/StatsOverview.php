<?php

namespace App\Filament\Company\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $oredrs = Order::where('company_id', auth()->user()->id);

        return [
            Stat::make('Revenue', '' . $oredrs->count())
                ->label(__('Revenue'))
                ->description(__('Orders'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Revenue', '$' . $oredrs->sum('cash_required'))
                ->label(__('Revenue'))
                ->description(__('Cash Required'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Revenue', '$' . $oredrs->sum('cash_required'))
                ->label(__('Revenue'))

                ->description(__('Total Amount'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            // Stat::make(__('New customers'), $formatNumber($newCustomers))
            //     ->description('3% decrease')
            //     ->descriptionIcon('heroicon-m-arrow-trending-down')
            //     ->chart([17, 16, 14, 15, 14, 13, 12])
            //     ->color('danger'),
            // Stat::make(__('New orders'), $formatNumber($newOrders))
            //     ->description('7% increase')
            //     ->descriptionIcon('heroicon-m-arrow-trending-up')
            //     ->chart([15, 4, 10, 2, 12, 4, 12])
            //     ->color('success'),
        ];
    }
}
