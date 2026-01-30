<?php

namespace Kfn\Base\Listeners\Cache;

use Illuminate\Cache\Events\KeyForgotten;
use Kfn\Base\Models\Cached;

class ForgottenListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(KeyForgotten $event): void
    {
        try {
            Cached::rawQuery()
                ->where('key', $event->key)
                ->where('store', $event->storeName)
                ->delete();
        } catch (\Throwable $tr) {
            $context = [
                'message' => $tr->getMessage(),
            ];
            if (config('app.debug')) {
                $context['trace'] = explode(PHP_EOL, $tr->getTraceAsString());
            }

            app('log')->error('cache-handling written error', $context);
        }
    }
}
