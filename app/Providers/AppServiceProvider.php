<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\Filament\ConsultationPanelProvider;
use App\Providers\Filament\FilamentAuthProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar los paneles y providers
        $this->app->register(ConsultationPanelProvider::class);
        $this->app->register(FilamentAuthProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
