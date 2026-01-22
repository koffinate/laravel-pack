<?php

namespace Kfn\Base\Listeners\Cache;

use Illuminate\Cache\Events\CacheFlushed;
use Kfn\Base\Models\Cached;

class FlushedListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(CacheFlushed $event): void
    {
        try {
            $cached = Cached::query()
                ->where('store', $event->storeName ?? config('cache.default', 'file'));

            if ($event->tags) {
                $cached->whereJsonContains('tags', $event->tags);
            }

            $cached->delete();

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
