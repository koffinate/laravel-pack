<?php

namespace Kfn\Base;

use Illuminate\Support\ServiceProvider;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/config.php' => config_path('koffinate/base.php')], 'config');
    }

    /** @inheritDoc */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'koffinate.base');
    }
}
