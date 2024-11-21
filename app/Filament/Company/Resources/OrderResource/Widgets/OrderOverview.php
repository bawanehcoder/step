<?php

namespace App\Filament\Company\Resources\OrderResource\Widgets;

use App\Filament\Company\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }
    protected function getStats(): array
    {
        $orderData = Trend::query(Order::where('company_id', auth()->user()->id))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            
            ->perMonth()
            ->count();

        return [
            Stat::make(__('Orders'), $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make(__('Open Orders'), $this->getPageTableQuery()->whereIn('order_status', ['open', 'processing'])->count()),
            
            Stat::make(__('Average Price'), number_format($this->getPageTableQuery()->avg('cash_required'), 2)),
        ];
    }
}
