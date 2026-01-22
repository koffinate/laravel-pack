<?php

namespace Kfn\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Kfn\Base\Models\Cached;

use function Laravel\Prompts\table;

class CacheInvalidate extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:invalidate '.
    '{stores? : Which stores to invalidate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invalidate expired cache';

    /** @var int */
    private static int $chunkIncr = 0;

    /** @var int */
    private static int $totalInvalidated = 0;

    /** @var int */
    private static int $totalFailed = 0;

    /** @var array */
    private static array $failKeys = [];

    /** @var array */
    private static array $tempFailKeys = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $stores = array_filter(explode(',', $this->argument('stores')), fn ($it) => !empty($it));
        $skipKeys = config('koffinate.base.cache.skip_invalidate_keys');

        $cached = Cached::query()
            ->select('id', 'key', 'store', 'expires_at')
            ->where('expires_at', '<', now()->toAtomString());

        if (!empty($skipKeys)) {
            is_array($skipKeys)
                ? $cached->whereNotIn('key', $skipKeys)
                : $cached->where('key', '!=', $skipKeys);
        }

        $fromStore = '';
        if (!empty($stores)) {
            $fromStore = ' from store(s) '.implode(', ', $stores);

            if (count($stores) === 1) {
                $cached->where('store', $stores[0]);
            } else {
                $cached->whereIn('store', $stores);
            }
        }

        $this->components->alert('Invalidating caches'.$fromStore);
        $this->newLine();

        if (!cacheIsHandling()) {
            $this->newLine();
            $this->components->warn('THIS FEATURE WAS DISABLED');
            $this->components->info('you can enable this feature via config "koffinate.base.cache.handling" or set KFN_CACHE_HANDLING to true on env');
            return;
        }

        Cached::$catchEvents = false;

        $cached->chunkById(100, function ($caches) {
            $ids = [];
            ++self::$chunkIncr;

            $this->components->task('invalidating cache #'.static::$chunkIncr, function () use ($caches, &$ids) {
                foreach ($caches as $cache) {
                    $cacheService = app('cache')->store($cache->store);

                    if ($cacheService->missing($cache->key)) {
                        $ids[] = $cache->id;
                    } elseif ($cacheService->forget($cache->key)) {
                        $ids[] = $cache->id;
                    } else {
                        ++static::$totalFailed;

                        if ($this->output->isVerbose()) {
                            self::$tempFailKeys[] = $cache->key;
                            if (count(self::$tempFailKeys) === 3) {
                                self::$failKeys[] = self::$tempFailKeys;
                                self::$tempFailKeys = [];
                            }
                        }
                    }
                }

                if (!empty($ids)) {
                    try {
                        if (Cached::query()->whereIn('id', $ids)->delete()) {
                            self::$totalInvalidated += count($ids);
                        }

                    } catch (\Throwable $tr) {
                        $context = [
                            'message' => $tr->getMessage(),
                        ];
                        if (config('app.debug')) {
                            $context['trace'] = explode(PHP_EOL, $tr->getTraceAsString());
                        }

                        app('log')->error('cache invalidating error', $context);
                    }
                }
            });
        });

        Cached::$catchEvents = true;

        $this->newLine();
        if (static::$totalInvalidated > 0) {
            $this->components->info('successfully invalidated '.static::$totalInvalidated.' cache(s).');
        } else {
            $this->components->info('nothing caches already invalidated.');
        }
        if (static::$totalFailed > 0) {
            $this->components->error('with '.static::$totalFailed.' cache(s) was failed to invalidate.');

            if ($this->output->isVerbose()) {
                if (count(self::$tempFailKeys) > 0) {
                    self::$failKeys[] = self::$tempFailKeys;
                    self::$tempFailKeys = [];
                }
                if (!empty(self::$failKeys)) {
                    table(['failed key', 'failed key', 'failed key'], self::$failKeys);
                }
            }
        }
    }
}
