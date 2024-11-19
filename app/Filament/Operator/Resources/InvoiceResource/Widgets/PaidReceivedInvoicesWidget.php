<?php

namespace App\Filament\Operator\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;

class PaidReceivedInvoicesWidget extends BaseWidget
{
    protected function getStats(): array
    {
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
        ];
    }
}
