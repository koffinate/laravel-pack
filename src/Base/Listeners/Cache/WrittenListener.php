<?php

namespace Kfn\Base\Listeners\Cache;

use Illuminate\Cache\Events\KeyWritten;
use Kfn\Base\Models\Cached;

class WrittenListener
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
    public function handle(KeyWritten $event): void
    {
        if (str($event->key)->doesntStartWith('illuminate:cache:flexible:created:')) {
            try {
                $tags = $event->tags ? (array) $event->tags : null;
                $data = collect([
                    'key' => $event->key,
                    'store' => $event->storeName,
                    'tags' => $tags,
                    'expiration' => $event->seconds ?? null,
                    // 'expires_at' => now()->addSeconds($event->seconds)->toAtomString(),
                ]);

                $cached = Cached::query()
                    ->select('id', 'renew')
                    ->where('key', $data->get('key'))
                    ->where('store', $data->get('store'));

                if (!empty($tags)) {
                    $cached->whereJsonContains('tags', $tags)
                        ->whereJsonLength('tags', count($tags));
                }

                $cached = $cached->first();

                if ($cached) {
                    $cached->update($data->only(['expiration'])->toArray());

                    return;
                }

                Cached::query()->create($data->toArray());
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
}
