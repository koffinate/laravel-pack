<?php

namespace Kfn\UI\Concerns;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Throwable;
use Yajra\DataTables\Contracts\DataTableButtons;

trait HasUI
{
    use InteractWithUILink;

    /**
     * Prefix View Path.
     *
     * @var string
     */
    protected string $viewDomain = '';
    protected string $viewPath = '';
    protected string $viewPrefix = '';

    /**
     * Controller data.
     *
     * @var array
     */
    private array $controllerData = [];

    /**
     * Page title.
     *
     * @var string
     */
    private string $pageTitle = '';

    /**
     * Page Meta.
     *
     * @var array
     */
    private array $pageMeta = [
        'description' => '',
        'keywords' => 'yusronarif, koffinate, laravel, php',
        'author' => 'Yusron Arif <yusron.arif4::at::gmail.com>',
        'generator' => 'Koffinate',
    ];

    /**
     * Breadcrumbs Collection.
     *
     * @var Collection
     */
    private Collection $breadCrumbs;

    /**
     * Reserved variable for the controller.
     *
     * @var array
     */
    private array $reservedVariables = ['pageTitle', 'pageMeta'];

    /**
     * Yajra Datatable wrapper
     *
     * @var DataTableButtons|null
     */
    private DataTableButtons|null $kfnTable = null;

    /**
     * Serve blade template.
     *
     * @param  string  $view
     *
     * @return View|\Inertia\Response
     */
    protected function view(string $view): View|\Inertia\Response
    {
        $this->share();

        if ($this->viewDomain) {
            $view = "{$this->viewDomain}::{$view}";
        }

        if ($this->viewPrefix) {
            $view = preg_replace('/(\.)+$/i', '', $this->viewPrefix).'.'.$view;
        }

        if (class_exists('Inertia\Inertia') && 'inertia' === config('koffinate.ui.breeze.type')) {
            return \Inertia\Inertia::render($view, $this->controllerData);
        }

        if (property_exists($this, 'kfnTable') && $this->kfnTable instanceof DataTableButtons) {
            return $this->kfnTable->render($view, $this->controllerData);
        }

        return view($view, $this->controllerData);
    }

    /**
     * Share Blade View.
     *
     * @return void
     */
    private function share(): void
    {
        if (method_exists($this, 'loadControllerButtons')) {
            $this->loadControllerButtons();
        }

        $this->setPageTitle($this->pageTitle ?: 'Untitled', true);

        $this->controllerData = array_merge(request()->route()->parameters(), $this->controllerData);

        $this->setPageMeta('csrf_token', csrf_token());

        $this->controllerData['activeUser'] = auth()->user();
        $this->controllerData['pageMeta'] = $this->pageMeta;
        $this->controllerData['breadCrumbs'] = $this->breadCrumbs ?? collect();

        $viewPath = $this->viewPath ?: $this->viewPrefix;
        $viewDomain = $this->viewDomain ? "{$this->viewDomain}::" : '';

        $this->controllerData['viewPath'] = $viewDomain.($viewPath ? "{$viewPath}." : '');
        // view()->share('viewPath', $this->controllerData['viewPath']);
    }

    /**
     * Set controller data.
     *
     * @param  string  $name
     * @param  mixed  $value
     *
     * @return static
     *
     * @throws \Exception
     */
    protected function setData(string $name, mixed $value): static
    {
        if (in_array($name, $this->reservedVariables)) {
            throw new Exception("Variable [$name] is reserved by this controller");
        }
        $this->controllerData[$name] = $value;

        return $this;
    }

    /**
     * Get controller data.
     *
     * @param  string|null  $name
     * @param  mixed  $default
     * @param  bool  $asFluent
     *
     * @return mixed
     * @throws Throwable
     */
    protected function getData(string|null $name = null, mixed $default = null, bool $asFluent = false): mixed
    {
        $data = fluent($this->controllerData);

        if ($name) {
            return $data->get($name, $default);
        }

        return $asFluent ? $data : $data->toArray();
    }

    /**
     * Alias for get controller data.
     *
     * @param  string|null  $name
     * @param  mixed  $default
     * @param  bool  $asFluent
     *
     * @return mixed
     * @throws Throwable
     */
    protected function data(string|null $name = null, mixed $default = null, bool $asFluent = false): mixed
    {
        return $this->getData($name, $default, $asFluent);
    }

    /**
     * Set Page title.
     *
     * @param  string  $title
     * @param  bool  $share
     *
     * @return static
     */
    protected function setPageTitle(string $title, bool $share = false): static
    {
        if ($share) {
            if (! empty($this->controllerData['pageTitle'])) {
                $title = $this->controllerData['pageTitle'];
            }
            view()->share('pageTitle', $title);
            unset($this->controllerData['pageTitle']);
        } else {
            $this->controllerData['pageTitle'] = $title;
        }
        return $this;
    }

    /**
     * Set page meta.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return static
     */
    protected function setPageMeta(string $key, mixed $value): static
    {
        $this->pageMeta[$key] = $value;
        return $this;
    }

    /**
     * Set BreadCrumb.
     *
     * @param  string|array  $breadcrumb
     *
     * @return static
     */
    protected function setBreadCrumb(string|array $breadcrumb): static
    {
        $this->breadCrumbs = collect();
        return $this->addBreadCrumb($breadcrumb);
    }

    /**
     * Add BreadCrumb.
     *
     * @param  string|array  $breadcrumb
     *
     * @return static
     */
    protected function addBreadCrumb(string|array $breadcrumb): static
    {
        foreach ((array) $breadcrumb as $val) {
            if (is_string($val)) {
                $this->breadCrumbs->add($this->breadCrumbFormat(['title' => $breadcrumb]));
                break;
            }
            $this->breadCrumbs->add($this->breadCrumbFormat($val));
        }
        return $this;
    }

    /**
     * Breadcrumb formatter.
     *
     * @param  array  $breadcrumb
     *
     * @return Fluent
     */
    private function breadCrumbFormat(array $breadcrumb): Fluent
    {
        $default = ['title' => '', 'url' => '#'];
        return new Fluent(
            array_merge($default, Arr::only($breadcrumb, ['title', 'url']))
        );
    }

    /**
     * Set Default Value for Request Input.
     *
     * @param string|array $name
     * @param mixed $value
     * @param bool $force
     *
     * @return void
     * @throws Throwable
     */
    protected function setDefault(string|array $name, mixed $value = null, bool $force = false): void
    {
        setDefaultRequest($name, $value, $force);
    }

    /**
     * @param  mixed  $table
     *
     * @return void
     * @throws Throwable
     */
    protected function setTable(mixed $table): void
    {
        if (!$table instanceof DataTableButtons) {
            throw_if(
                app()->hasDebugModeEnabled(),
                new Exception('Table must be instance of Yajra\DataTables\Contracts\DataTableButtons')
            );
            return;
        }

        if (request()->ajax() && request()->wantsJson()) {
            $table->render()->send();
            exit();
        }
        $this->kfnTable = $table;
    }
}
