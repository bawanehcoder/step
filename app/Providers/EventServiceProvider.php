<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\OrderUpdated;
use App\Listeners\LogOrderUpdate;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderUpdated::class => [
            LogOrderUpdate::class,
        ],
    ];

    public function boot()
    {
        //
    }
}