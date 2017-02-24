<?php

namespace GillidandaWeb\ValidationGenerator;

use Illuminate\Support\ServiceProvider;

class ValidationGeneratorServiceProvider extends ServiceProvider
{

    protected $commands = [
        'GillidandaWeb\ValidationGenerator\Console\Commands\GenerateValidationRules'
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
