<?php

namespace CodeCubes\DataTable\Providers;

use Illuminate\Support\ServiceProvider;

class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/datatable.php' => config_path('codecubes.datatable.php'),
            __DIR__ . '/../../resources/assets' => public_path('codecubes/datatable'),
        ]);

        $this->loadViewsFrom( __DIR__ . '/../../resources/views', 'datatable' );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}