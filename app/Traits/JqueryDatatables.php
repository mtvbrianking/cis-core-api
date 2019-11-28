<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait JqueryDatatables
{
    /**
     * Restructure request query parameters for eloquent builder.
     *
     * @param array $params
     * @param array $relationships
     *
     * @return array
     */
    public static function prepareQueryParameters(array $params, array $relationships = []): array
    {
        $join = $select = $orWhere = $orderBy = [];

        foreach ($params['columns'] as $col) {
            if (! isset($col['name'])) {
                continue;
            }

            $parts = explode('.', $col['name']);

            if (count($parts) === 1) {
                // Select

                $select[] = $col['name'];

                // Filter

                if (! (bool) $col['searchable']) {
                    continue;
                }

                if (! $term = array_get($col['search'], 'value')) {
                    if (! $term = array_get($params['search'], 'value')) {
                        continue;
                    }
                }

                $orWhere[] = [
                    'field' => $col['name'],
                    'operator' => 'ilike',
                    'value' => "%{$term}%",
                ];

                continue;
            }

            // ------------------------------------
            // Related
            // ------------------------------------

            // Select

            $field = array_pop($parts);
            $relation = array_pop($parts);

            $join[$relation]['select'][] = $field;

            // Filter

            if (! (bool) $col['searchable']) {
                continue;
            }

            if (! $term = array_get($col['search'], 'value')) {
                if (! $term = array_get($params['search'], 'value')) {
                    continue;
                }
            }

            $join[$relation]['or-where'][] = [
                'field' => $field,
                'operator' => 'ilike',
                'value' => "%{$term}%",
            ];
        }

        // Sort

        foreach ($params['order'] as $order) {
            $colIdx = $order['column'];

            $col = $params['columns'][$colIdx];

            if (! $col['orderable']) {
                continue;
            }

            if (! isset($col['name'])) {
                continue;
            }

            $parts = explode('.', $col['name']);

            if (count($parts) === 1) {
                $orderBy[] = [
                    'field' => $col['name'],
                    'direction' => $order['dir'],
                ];

                continue;
            }

            // ------------------------------------
            // Related
            // ------------------------------------

            $field = array_pop($parts);
            $relation = array_pop($parts);

            $join[$relation]['order-by'][] = [
                'field' => $field,
                'direction' => $order['dir'],
            ];
        }

        // Relationships

        foreach ($relationships as $relation => $mapping) {
            if (isset($join[$relation])) {
                $pk = $mapping['pk'];
                if (! in_array($pk, $join[$relation]['select'])) {
                    $join[$relation]['select'][] = $pk;
                }

                $fk = $mapping['fk'];
                if (! in_array($fk, $select)) {
                    $select[] = $fk;
                }
            }
        }

        return [
            'join' => $join,
            'select' => $select,
            'or-where' => $orWhere,
            'order-by' => $orderBy,
            'offset' => $params['start'],
            'limit' => $params['length'],
            'draw' => $params['draw'],
        ];
    }

    /**
     * Query database for jQuery datatables.
     *
     * @see https://www.phpflow.com/jquery/data-table-table-plug-in-for-jquery/amp Basic usage
     * @see https://phppot.com/php/column-search-in-datatables-using-server-side-processing Server-side processing
     * @see https://datatables.net/forums/discussion/comment/116124/#Comment_116124 Flattening data
     * @see https://stackoverflow.com/a/43783574 Eloquent orderBy relation
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $constraints
     *
     * @return \Illuminate\Http\Response
     */
    public static function queryForDatatables(Builder $query, array $constraints): Response
    {
        $isFiltered = false;

        // Available records

        $availableRecords = $query->count();

        // Include relations

        foreach (array_get($constraints, 'join', []) as $relation => $options) {
            if (isset($options['select'])) {
                $fields = $options['select'];

                $query->with(["{$relation}" => function ($query) use ($fields) {
                    $query->select($fields);
                }]);
            } else {
                $query->with($relation);
            }

            if (isset($options['where'])) {
                $isFiltered = true;
                $query->whereHas("{$relation}", function ($query) {
                    foreach ($options['where'] as $filter) {
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

            if (isset($options['or-where'])) {
                $isFiltered = true;
                $query->orWhereHas("{$relation}", function ($query) {
                    foreach ($options['or-where'] as $filter) {
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

        // Select fields

        if ($fields = array_get($constraints, 'select')) {
            $query->select($fields);
        }

        // Filter

        foreach (array_get($constraints, 'where', []) as $filter) {
            $isFiltered = true;

            if ($filter['operator'] == 'in') {
                $query->whereIn($filter['field'], $filter['value']);
            } elseif ($filter['operator'] == 'between') {
                $query->whereBetween($filter['field'], $filter['value']);
            } else {
                $query->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        foreach (array_get($constraints, 'or-where', []) as $filter) {
            $isFiltered = true;

            if ($filter['operator'] == 'in') {
                $query->orWhereIn($filter['field'], $filter['value']);
            } elseif ($filter['operator'] == 'between') {
                $query->orWhereBetween($filter['field'], $filter['value']);
            } else {
                $query->orWhere($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        // Sort

        foreach (array_get($constraints, 'order-by', []) as $sort) {
            $query->orderBy($sort['field'], $sort['direction']);
        }

        // Paginate

        $query->skip($constraints['offset']);

        // Limit

        $query->take($constraints['limit']);

        // Matched records

        $matchedRecords = $query->get();

        // Order by relations
        foreach (array_get($constraints, 'join', []) as $relation => $options) {
            foreach (array_get($options, 'order-by', []) as $sort) {
                if ($sort['direction'] == 'asc') {
                    $matchedRecords->sortBy("{$relation}.{$sort['field']}");

                    continue;
                }

                $matchedRecords->sortByDesc("{$relation}.{$sort['field']}");
            }
        }

        return response([
            'draw' => ++$constraints['draw'],
            'recordsTotal' => $availableRecords,
            'recordsFiltered' => $isFiltered ? $matchedRecords->count() : $availableRecords,
            'data' => $matchedRecords,
        ]);
    }
}
