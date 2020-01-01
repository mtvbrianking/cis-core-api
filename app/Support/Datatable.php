<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class Datatable
{
    /**
     * Restructure jquery datatables' request query parameters for eloquent builder.
     *
     * @param array  $params
     *
     * @return array
     */
    public static function prepareQueryParameters(array $params): array
    {
        $constraints = [];

        foreach ($params['columns'] as $col) {
            // Select

            if (! isset($col['name'])) {
                continue;
            }

            $constraints['select'][] = $col['name'];

            // Search

            if (! (bool) $col['searchable']) {
                continue;
            }

            // Column search

            if ($term = array_get($col['search'], 'value')) {
                $constraints['where'][] = [
                    'type' => 'and',
                    'field' => $col['name'],
                    'operator' => 'ilike',
                    'value' => "%{$term}%",
                ];
            }

            // Global search

            if ($term = array_get($params['search'], 'value')) {
                $constraints['where-group'][0]['type'] = 'and';
                $constraints['where-group'][0]['where'][] = [
                    'type' => 'or',
                    'field' => $col['name'],
                    'operator' => 'ilike',
                    'value' => "%{$term}%",
                ];
            }

            continue;
        }

        // Sort
        $constraints['order-by'] = static::extraSort($params);

        // Paginate
        $constraints['offset'] = $params['start'];

        // Limit
        $constraints['limit'] = $params['length'];

        // Draw
        $constraints['draw'] = $params['draw'];

        return $constraints;
    }

    public static function extraTables(array $columns): array
    {
        $tables = [];

        foreach ($columns as $column) {
            $parts = explode('.', $column);

            $table = null;

            if (count($parts) > 1) {
                $table = $parts[0];
            }

            if (in_array($table, $tables)) {
                continue;
            }

            $tables[] = $table;
        }

        return $tables;
    }

    public static function extraSort(array $params): array
    {
        $orderBy = [];

        foreach ($params['order'] as $order) {
            $colIdx = $order['column'];

            $col = $params['columns'][$colIdx];

            if (! $col['orderable']) {
                continue;
            }

            if (! isset($col['name'])) {
                continue;
            }

            $orderBy[] = [
                'field' => $col['name'],
                'direction' => $order['dir'],
            ];
        }

        return $orderBy;
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            $find = $filter['type'] == 'or' ? 'orWhere' : 'where';

            if ($filter['operator'] == 'in') {
                $query->{$find.'In'}($filter['field'], $filter['value']);
            } elseif ($filter['operator'] == 'between') {
                $query->{$find.'Between'}($filter['field'], $filter['value']);
            } else {
                $query->{$find}($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        return $query;
    }

    public static function applyGroupedFilters(Builder $query, array $groups): Builder
    {
        foreach ($groups as $group) {
            $find = $group['type'] == 'or' ? 'orWhere' : 'where';
            $filters = $group['where'];
            $query->{$find}(function ($query) use ($filters) {
                static::applyFilters($query, $filters);
            });
        }

        return $query;
    }

    public static function prefixFields(array $fields): array
    {
        $rawFields = [];
        foreach ($fields as $field) {
            $rawFields[] = "{$field} AS {$field}";
        }

        return $rawFields;
    }

    public static function sqlToModel(array $rows, array $tblModel): array
    {
        $_data = [];
        foreach ($rows as $row) {
            $_datum = [];
            foreach ($row as $key => $value) {
                $tblField = explode('.', $key);
                $table = $tblField[0];
                $field = $tblField[1];
                $model = $tblModel[$table];
                if (is_null($model)) {
                    $_datum[$field] = $value;

                    continue;
                }
                $_datum[$model][$field] = $value;
            }
            $_data[] = $_datum;
        }

        return $_data;
    }
}
