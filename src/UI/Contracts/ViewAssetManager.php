<?php

namespace Kfn\UI\Contracts;

use Illuminate\Support\HtmlString;

interface ViewAssetManager
{
    /**
     * Initialize Assets.
     *
     * @return self
     */
    public static function init(): static;

    /**
     * Add Assets.
     *
     * @param  string|array  $name
     * @param  string  $source
     *
     * @return $this
     */
    public function add(string|array $name, string $source = 'local'): static;

    /**
     * Check assets already loaded.
     *
     * @param  string|array  $name
     *
     * @return bool
     */
    public function loaded(string|array $name): bool;

    /**
     * Build Assets.
     *
     * @return void
     */
    public function build(): void;

    /**
     * Get Script Assets.
     *
     * @return HtmlString
     */
    public static function script(): HtmlString;

    /**
     * Get Script Assets.
     * alias of script().
     *
     * @return HtmlString
     */
    public static function js(): HtmlString;

    /**
     * Get Style Assets.
     *
     * @return HtmlString
     */
    public static function style(): HtmlString;

    /**
     * Get Style Assets.
     * alias of style().
     *
     * @return HtmlString
     */
    public static function css(): HtmlString;

    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    public static function document(string $path): string;

    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    public static function vendor(string $path): string;
}
