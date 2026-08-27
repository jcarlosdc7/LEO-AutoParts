<?php

namespace App\Providers;

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Livewire::addPersistentMiddleware([EnsureActiveUser::class, RoleMiddleware::class]);

        // Carga el idioma almacenado en la sesión o usa el predeterminado
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);
    }
}
