<?php

namespace Kfn\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Pagination\Paginator;

class Builder extends BaseBuilder
{
    /** @inheritdoc */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = value($total) ?? $this->toBase()->getCountForPagination($columns);

        $perPage = value($perPage, $total) ?: $this->model->getPerPage();

        $results = $total
            ? $this->forPage($page, $perPage)->get()
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
