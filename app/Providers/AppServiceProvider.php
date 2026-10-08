<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\View::composer(
            ['partials.admin.sidebar', 'partials.admin.topbar'],
            function ($view) {
                $openAlertsCount = 0;
                $criticalAlertsCount = 0;
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('alerts')) {
                        $openAlertsCount = \App\Models\Alert::open()->count();
                        $criticalAlertsCount = \App\Models\Alert::open()->critical()->count();
                    }
                } catch (\Throwable) {
                    $openAlertsCount = 0;
                    $criticalAlertsCount = 0;
                }

                $view->with([
                    'openAlertsCount'     => $openAlertsCount,
                    'criticalAlertsCount' => $criticalAlertsCount,
                ]);
            }
        );
    }
}
