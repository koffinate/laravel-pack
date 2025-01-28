<?php

namespace Kfn\UI;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/ui.php', 'koffinate.ui');
        $this->mergeConfigFrom(__DIR__.'/config/plugins.php', 'koffinate.plugins');

        // $this->app->register(DbServiceProvider::class, true);
        // $this->app->register(BladeServiceProvider::class, true);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/config/ui.php' => config_path('koffinate/ui.php')], 'config');
        $this->publishes([__DIR__.'/config/plugins.php' => config_path('koffinate/plugins.php')], 'config');

        $this->blades();
    }

    private function blades(): void
    {
        Blade::if('hasSections', function (string ...$sections): bool {
            $view = app('view');
            return (bool) collect($sections)->first(function ($section) use ($view) {
                return ! empty(trim($view->yieldContent($section)));
            });
        });

        Blade::directive('plugins', function ($arguments) {
            return "<?php plugins( {$arguments} ); ?>";
        });
    }
}
