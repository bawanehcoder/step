<?php

namespace App\Filament\Company\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class CustomerChart extends ChartWidget
{
    protected static ?string $heading = 'Customers per month';

    public function getHeading(): string | Htmlable | null
    {
        return __('Customers Per Month');
    }

    protected static ?int $sort = 1;

    protected function getData(): array
    {

        $start = Carbon::parse(Customer::where('company_id', auth()->user()->id)->min('created_at'));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, '1 month', $end);

        $usersPerMonth = collect($period)->map(function ($date) {
            $endDate = $date->copy()->endOfDay();

            // dd($endDate);

            return [
                'count' => Customer::where('created_at', '<=', $endDate)
                    ->where('created_at', '>=', $date->copy()->startOfDay())
                    ->count(),
                'month' => $endDate->format('Y-m-d'),
            ];
        });

        $data = $usersPerMonth->pluck('count')->toArray();
        $labels = $usersPerMonth->pluck('month')->toArray();
        return [
            'datasets' => [
                [
                    'label' =>__( 'Customers'),
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
