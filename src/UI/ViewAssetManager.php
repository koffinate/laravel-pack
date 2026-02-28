<?php

namespace Kfn\UI;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\HtmlString;

class ViewAssetManager implements \Kfn\UI\Contracts\ViewAssetManager
{
    /** @var bool */
    public static bool $built = false;

    /** @var Collection<string, string>|null */
    private static Collection|null $assets = null;

    /** @var Collection<string, string>|null */
    private static Collection|null $availablePlugins = null;

    /** @var Fluent|null */
    private static Fluent|null $readyPlugins = null;

    /** @var string */
    private static string $assetType = '';

    /** @var string */
    private static string $scriptType = 'text/javascript';

    /** @var string */
    private string $httpPattern = '/^(http[s?]:)/i';

    /**
     * New asset manager instance.
     */
    public function __construct()
    {
        if (! self::$assets) {
            self::$assets = collect();
            self::$availablePlugins = collect(config('koffinate.plugins.items'));
            self::$readyPlugins = new Fluent;

            $assetType = config('koffinate.plugins.asset_type');
            $scriptType = config('koffinate.plugins.script_type');

            if (in_array($assetType, ['vite', 'mix'])) {
                self::$assetType = $assetType;
            }
            if (in_array($scriptType, ['module', 'text/javascript'])) {
                self::$scriptType = $scriptType;
            }
        }
    }

    /**
     * Initialize Assets.
     *
     * @return self
     */
    public static function init(): static
    {
        return new static;
    }

    /**
     * Add Assets.
     *
     * @param  array|string  $name
     * @param  string  $source
     *
     * @return $this
     */
    public function add(array|string $name, string $source = 'local'): static
    {
        if (in_array($source, ['local', 'vendor'])) {
            $availablePlugins = self::$availablePlugins->filter(fn ($it, $key) => in_array($key, (array) $name));
            if ($availablePlugins->isNotEmpty()) {
                self::$assets = self::$assets->merge(
                    $availablePlugins->map(fn ($it) => fluent($it)->set('source', $source))
                );
            }
        }

        return $this;
    }

    /**
     * Check assets already loaded.
     *
     * @param  array|string  $name
     *
     * @return bool
     */
    public function loaded(array|string $name): bool
    {
        return self::$assets->has($name);
    }

    /**
     * Build Assets.
     *
     * @return void
     */
    public function build(): void
    {
        if (self::$assetType === 'vite') {
            $this->buildViteAsset();
        }
        else {
            $this->buildPluginAsset();
        }

        static::$built = true;
    }

    /**
     * Get Script Assets.
     *
     * @return HtmlString
     */
    public static function script(): HtmlString
    {
        if (! self::$built) {
            static::init()->build();
        }

        return new HtmlString(self::$readyPlugins->get('js', ''));
    }

    /**
     * Get Script Assets.
     * alias of script().
     *
     * @return HtmlString
     */
    public static function js(): HtmlString
    {
        return self::script();
    }

    /**
     * Get Style Assets.
     *
     * @return HtmlString
     */
    public static function style(): HtmlString
    {
        if (! self::$built) {
            static::init()->build();
        }

        return new HtmlString(self::$readyPlugins->get('css', ''));
    }

    /**
     * Get Style Assets.
     * alias of style().
     *
     * @return HtmlString
     */
    public static function css(): HtmlString
    {
        return self::style();
    }

    /**
     * Retrieve Application Plugin's Assets.
     * retrieving from config's definitions.
     *
     * @return void
     */
    private function buildViteAsset(): void
    {
        if (class_exists(\Illuminate\Foundation\Vite::class)) {
            return;
        }
        $result = new Fluent;

        self::$assets->whenNotEmpty(function (Collection $assets) use (&$result) {
            $vite = app(\Illuminate\Foundation\Vite::class);
            $scriptType = self::$scriptType;

            $assets->each(function ($asset) use ($scriptType, $vite, &$result) {
                foreach (['css', 'js'] as $assetType) {
                    foreach ($asset->get($assetType) as $path) {
                        $result[$assetType] .= preg_match($this->httpPattern, $path)
                            ? (
                                $assetType === 'css'
                                    ? "<link href='{$path}' rel='stylesheet'>"
                                    : "<script type='{$scriptType}' src='{$path}'></script>"
                            )
                            : $vite($path)->toHtml();
                    }
                }
            });
        });

        static::$readyPlugins = $result;
    }

    /**
     * Retrieve Application Plugin's Assets.
     * retrieving from config's definitions.
     *
     * @return void
     */
    private function buildPluginAsset(): void
    {
        $result = new Fluent;

        self::$assets->whenNotEmpty(function (Collection $assets) use (&$result) {
            $scriptType = self::$scriptType;
            $localPath = preg_replace('/\/+$/', '', config('koffinate.plugins.base_path', 'plugins')).'/';

            $assets->each(function ($asset) use ($scriptType, $localPath, &$result) {
                if (! in_array($asset->get('source'), ['vendor', 'local'])) {
                    return;
                }

                $legacyOpen = '';
                $legacyClose = '';
                if (! empty($legacy = $asset->get('legacy.condition'))) {
                    $legacyOpen = $legacy[0] ?? '';
                    $legacyClose = $legacy[1] ?? '';
                }

                foreach (['css', 'js'] as $assetType) {
                    $pluginAsset = '';
                    foreach ($asset->get($assetType) as $path) {
                        $path = preg_match($this->httpPattern, $path) ? $path : (
                            $asset->get('source') === 'vendor'
                                ? vendor($path)
                                : cachedAsset($localPath.$path)
                        );

                        $pluginAsset .= $assetType === 'css'
                            ? "<link href='{$path}' rel='stylesheet'>"
                            : "<script type='{$scriptType}' src='{$path}'></script>";
                    }

                    $result[$assetType] .= $legacyOpen.$pluginAsset.$legacyClose;
                }
            });
        });

        static::$readyPlugins = $result;
    }

    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     *
     * @return string
     */
    public static function document(string $path): string
    {
        return cachedAsset(config('koffinate.ui.url.document', '/files')."/{$path}");
    }

    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     *
     * @return string
     */
    public static function vendor(string $path): string
    {
        $vendorPath = config('koffinate.ui.url.vendor');
        $vendorPath = $vendorPath !== '' ? $vendorPath : '/vendor';

        if (preg_match('/(:\/\/)+/i', $path, $matches, PREG_UNMATCHED_AS_NULL, 1)) {
            $pattern = ['/^(vendor:\/\/)/i'];
            $isVendorPath = preg_match($pattern[0], $path, flags: PREG_UNMATCHED_AS_NULL, offset: 1);
            $pattern[] = '/^(asset:\/\/)/i';
            $replacedCount = 0;

            $path = preg_replace($pattern, '', $path, -1, $replacedCount);
            if ($replacedCount > 0) {
                $vendorPath = $isVendorPath ? $vendorPath.'/assets' : '';
            }
        }

        if (isDev() && preg_match('/(app)((\.min)?\.css)$/i', $path)) {
            $path = preg_replace('/(app)((\.min)?\.css)$/i', '$1-dev$2', $path);
        }

        return cachedAsset($vendorPath.'/'.$path);
    }
}
