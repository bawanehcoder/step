<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $oredrs = Order::all();
        $drivers = Driver::all();
        $companeis = Company::all();

        $totalPaidReceived = Invoice::where('type', 'received')->where('status', 'paid')->sum('amount');
        $totalPaidIssued = Invoice::where('type', 'issued')->where('status', 'paid')->sum('amount');


        $profit = $totalPaidReceived - $totalPaidIssued;



        return [


            Card::make('Total Paid Received Invoices', $totalPaidReceived)
                ->description('Received invoices paid')
                ->color('success'),
            Card::make('Total Paid Issued Invoices', $totalPaidIssued)
                ->description('Issued invoices paid')
                ->color('primary'),
            Card::make('Profit', $profit)
                ->description('Difference between paid received and paid issued')
                ->color('success'),

            Stat::make('Orders', $oredrs->count())
                ->description('Orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

                Stat::make('Drivers', $drivers->count())
                ->description('Drivers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

                Stat::make('Company', $companeis->count())
                ->description('Company')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

        ];
    }
}
