<?php

namespace Kfn\UI;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Kfn\UI\Contracts\IKfnUiException;

class UiServiceProvider extends ServiceProvider
{
    public static IKfnUiException|null $kfnException = null;

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

        $this->app->singleton('kfnExceptionMessage', function (): IKfnUiException {
            KfnUiException::set();

            return new KfnUiException();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/config/ui.php' => config_path('koffinate/ui.php')], 'koffinate-ui-config');
        $this->publishes([__DIR__.'/config/plugins.php' => config_path('koffinate/plugins.php')], 'koffinate-ui-config');
        $this->publishes([__DIR__.'/views/components' => resource_path('views/vendor/koffinate/ui/components')], 'koffinate-ui-resource');

        $this->blades();
    }

    private function blades(): void
    {
        $customComponentDir = resource_path('views/vendor/koffinate/ui/components');
        Blade::anonymousComponentPath($customComponentDir, 'kfn');
        Blade::anonymousComponentPath(__DIR__.'/views/components', 'kfn');

        Blade::if('hasSections', function (string|array $sections): bool {
            if (is_string($sections)) {
                $sections = array_map(fn ($it) => trim($it), explode(',', $sections));
            }

            return hasSections($sections);
        });

        Blade::if('hasStack', function ($stackName) {
            if (is_string($stackName)) {
                $stackName = array_map(fn ($it) => trim($it), explode(',', $sections));
            }

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
