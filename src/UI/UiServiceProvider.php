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
        $this->publishes([__DIR__.'/config/ui.php' => config_path('koffinate/ui.php')], 'koffinate_config');
        $this->publishes([__DIR__.'/config/plugins.php' => config_path('koffinate/plugins.php')], 'koffinate_config');

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

        Blade::if('hasStack', function($stackName) {
            return hasStack($stackName);
        });

        Blade::directive('plugins', function ($arguments) {
            if (! str($arguments)->contains(['[', ':'])) {
                $arguments = "[{$arguments}]";
            }
            return "<?php plugins({$arguments}); ?>";
        });

        Blade::directive('method_if', function ($arguments) {
            $arguments = explode(',', $arguments, 3);
            $condition = $arguments[0] ?? false;
            $method = $arguments[1] ?? 'GET';
            return "<?= methodIf({$condition}, {$method}); ?>";
        });

        Blade::directive('feedback', function ($args) {
            return "<?php echo feedbackInput({$args});?>";
        });

        Blade::directive('feedbackClass', function ($args) {
            return "<?php echo feedbackClass({$args});?>";
        });
    }
}
