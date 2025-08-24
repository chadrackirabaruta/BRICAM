<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider; 
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            // ✅ Load API routes
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // ✅ Load Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}