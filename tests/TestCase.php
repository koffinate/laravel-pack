<?php

namespace Kfn\Tests;

use Illuminate\Foundation\Application;
use Kfn\Base\BaseServiceProvider;
use Kfn\UI\UiServiceProvider;
use Kfn\Util\UtilServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  Application  $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            UtilServiceProvider::class,
            BaseServiceProvider::class,
            UiServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     *
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('koffinate.ui.exception.enabled', true);
    }
}
