<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per month';

    protected static ?int $sort = 1;

    protected function getData(): array
    {

        $start = Carbon::parse(Order::min('created_at'));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, '1 month', $end);

        $usersPerMonth = collect($period)->map(function ($date) {
            $endDate = $date->copy()->endOfDay();

            // dd($endDate);

            return [
                'count' => Order::where('created_at', '<=', $endDate)
                    ->where('created_at', '>=', $date->copy()->startOfDay())
                    ->sum('cash_required'),
                'month' => $endDate->format('Y-m-d'),
            ];
        });

        $data = $usersPerMonth->pluck('count')->toArray();
        $labels = $usersPerMonth->pluck('month')->toArray();
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
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
