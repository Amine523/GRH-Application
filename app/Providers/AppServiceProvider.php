<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(UrlGenerator $url): void
    {
        $this->app->bind(ValidatePropertiesDataPipe::class, PrecognitivelyValidatePropertiesDataPipe::class);
//        if (config('app.env') !== 'local') {
        $url->forceScheme('https');
//        }
    }
}
