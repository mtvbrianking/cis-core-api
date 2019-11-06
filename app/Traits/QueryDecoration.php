<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait QueryDecoration
{
    /**
     * Build eloquent query from request query parameters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     *
     * @link https://m.dotdev.co/writing-advanced-eloquent-search-query-filters-de8b6c2598db Tutorial
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyConstraintsToQuery(Builder $query, Request $request)
    {
        // Include relations

        if ($request->filled('with')) {
            foreach ($request->with as $join) {
                $relation = $join['relation'];

                if (isset($join['select'])) {
                    $fields = $join['select'];

                    $query->with(["{$relation}" => function ($query) use ($fields) {
                        $query->select($fields);
                    }]);
                } else {
                    $query->with($relation);
                }
            }
        }

        // Select fields

        if ($request->filled('select')) {
            $query->select($request->select);
        }

        // Filter

        if ($request->filled('where')) {
            foreach ($request->where as $filter) {
                if ($filter['operator'] == 'in') {
                    $query->whereIn($filter['field'], $filter['value']);
                } elseif ($filter['operator'] == 'between') {
                    $query->whereBetween($filter['field'], $filter['value']);
                } else {
                    $query->where($filter['field'], $filter['operator'], $filter['value']);
                }
            }
        }

        // Sort

        if ($request->filled('sort')) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        return $query;
    }
}
