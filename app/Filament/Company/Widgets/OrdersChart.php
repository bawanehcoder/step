<?php

namespace App\Filament\Company\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'orders_per_month';
    public function getHeading(): string | Htmlable | null
    {
        return __('Orders Per Month');
    }

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $start = Carbon::parse(Order::where('company_id', auth()->user()->id)->min('created_at'));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, '1 month', $end);

        $ordersPerMonth = collect($period)->map(function ($date) {
            $endDate = $date->copy()->endOfDay();

            return [
                'count' => Order::where('created_at', '<=', $endDate)
                    ->where('created_at', '>=', $date->copy()->startOfDay())
                    ->sum('cash_required'),
                'month' => $endDate->format('Y-m-d'),
            ];
        });

        $data = $ordersPerMonth->pluck('count')->toArray();
        $labels = $ordersPerMonth->pluck('month')->toArray();
        
        return [
            'datasets' => [
                [
                    'label' => __('Orders'), 
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
