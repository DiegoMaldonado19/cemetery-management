<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\Filament\ConsultationPanelProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Solo registramos el panel de consulta - eliminamos la referencia a FilamentAuthProvider
        $this->app->register(ConsultationPanelProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
