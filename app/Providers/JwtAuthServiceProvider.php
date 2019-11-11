<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //Cargamos o requerimos la clase que necesitamos como helper

        require_once app_path() . '/Helpers/JwtAuth.php';

    }
}
