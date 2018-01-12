<?php

namespace DeveoDK\Core\Manager;

use Illuminate\Support\ServiceProvider;

class ManagerServiceProvider extends ServiceProvider
{
    /**
     * Register service providers
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/manager.php', 'core.manager');
    }

    /*
     * Application boot method
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/manager.php' => config_path('core/manager.php'),
        ]);
    }
}
