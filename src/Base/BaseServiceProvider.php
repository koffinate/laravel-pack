<?php

namespace Kfn\Base;

use Illuminate\Support\ServiceProvider;
use Kfn\Base\Exceptions\KfnException;
use Symfony\Component\VarDumper\VarDumper;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/config.php' => config_path('koffinate/base.php')], 'koffinate-base-config');

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
