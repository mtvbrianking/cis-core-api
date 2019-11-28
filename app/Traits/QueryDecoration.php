<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait QueryDecoration
{
    /**
     * Build eloquent query from request query parameters.
     *
     * @see https://stackoverflow.com/a/43783574 Order By eager loaded relation.
     * @see https://m.dotdev.co/writing-advanced-eloquent-search-query-filters-de8b6c2598db Tutorial
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyConstraintsToQuery(Builder $query, Request $request): Builder
    {
        // Include relations

        if ($request->filled('join')) {
            foreach ($request->with as $relation => $join) {
                if (isset($join['select'])) {
                    $fields = $join['select'];

                    $query->with(["{$relation}" => function ($query) use ($fields) {
                        $query->select($fields);
                    }]);
                } else {
                    $query->with($relation);
                }

                if (isset($join['where'])) {
                    $query->whereHas("{$relation}", function ($query) {
                        foreach ($join['where'] as $filter) {
                            if ($filter['operator'] == 'in') {
                                $query->whereIn($filter['field'], $filter['value']);
                            } elseif ($filter['operator'] == 'between') {
                                $query->whereBetween($filter['field'], $filter['value']);
                            } else {
                                $query->where($filter['field'], $filter['operator'], $filter['value']);
                            }
                        }
                    });
                }

                if (isset($join['or-where'])) {
                    $query->orWhereHas("{$relation}", function ($query) {
                        foreach ($join['or-where'] as $filter) {
                            if ($filter['operator'] == 'in') {
                                $query->whereIn($filter['field'], $filter['value']);
                            } elseif ($filter['operator'] == 'between') {
                                $query->whereBetween($filter['field'], $filter['value']);
                            } else {
                                $query->where($filter['field'], $filter['operator'], $filter['value']);
                            }
                        }
                    });
                }
            }
        }

        // Select

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

        if ($request->filled('or-where')) {
            foreach ($request->{'or-where'} as $filter) {
                if ($filter['operator'] == 'in') {
                    $query->orWhereIn($filter['field'], $filter['value']);
                } elseif ($filter['operator'] == 'between') {
                    $query->orWhereBetween($filter['field'], $filter['value']);
                } else {
                    $query->orWhere($filter['field'], $filter['operator'], $filter['value']);
                }
            }
        }

        // Sort

        if ($request->filled('order-by')) {
            foreach ($request->{'order-by'} as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        return $query;
    }
}
