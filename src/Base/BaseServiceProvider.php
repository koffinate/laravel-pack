<?php

namespace Kfn\Base;

use Illuminate\Cache\Events as CacheEvents;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kfn\Base\Exceptions\KfnException;
use Kfn\Base\Listeners\Cache as CacheListeners;
use Symfony\Component\VarDumper\VarDumper;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/config.php' => config_path('koffinate/base.php')], 'koffinate-base-config');
        $this->replaceConfigRecursivelyFrom(__DIR__.'/config.php', 'cache');
        $this->commands([
            Console\CacheInvalidate::class,
        ]);

        if (cacheIsHandling()) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');

            Event::listen(CacheEvents\KeyWritten::class, CacheListeners\WrittenListener::class);
            Event::listen(CacheEvents\KeyForgotten::class, CacheListeners\ForgottenListener::class);
            Event::listen(CacheEvents\CacheFlushed::class, CacheListeners\FlushedListener::class);
            Event::listen('cache:*', function (string $eventName, array $data) {
                if ('cache:cleared' === $eventName) {
                    [$storeName, $tags] = $data;
                    $listen = new CacheListeners\FlushedListener();
                    $listen->handle(new CacheEvents\CacheFlushed($storeName, $tags));
                }
            });
        }

        if (KfnException::shouldRenderException()) {
            try {
                $dumpServerHost = str($_SERVER['VAR_DUMPER_SERVER'] ?? '127.0.0.1:9912')->replaceMatches(
                    '/^.*:\/\//',
                    ''
                );
                $dumpServerStarted = stream_socket_client('tcp://'.$dumpServerHost->toString());
            } catch (\Throwable $th) {
                $dumpServerStarted = false;
            }

            if (! $dumpServerStarted) {
                VarDumper::setHandler(function ($var, ?string $label = null) {
                    if (null !== $label) {
                        $var = array_merge(['label' => $label], $var);
                    }

                    response()->json($var)->send();
                });
            }
        }
    }

    /** @inheritDoc */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'koffinate.base');
    }
}
