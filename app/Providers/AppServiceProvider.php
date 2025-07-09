<?php

namespace App\Providers;

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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * @OA\Info(
     *     version="1.0.0",
     *     title="Koupii LMS API",
     *     description="API documentation for English course LMS",
     *     @OA\Contact(
     *         email="support@koupii.com"
     *     )
     * )
     */
}
