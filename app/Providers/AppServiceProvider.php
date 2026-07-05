<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Event::listen(
            \App\Events\TaskStatusChanged::class,
            \App\Listeners\SendTaskNotification::class
        );
        Event::listen(
            \App\Events\TaskStatusChanged::class,
            \App\Listeners\WriteTaskAuditLog::class
        );
    }
}
