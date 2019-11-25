<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait JqueryDatatables
{
    /**
     * Query database for jQuery datatables.
     *
     * @see https://www.phpflow.com/jquery/data-table-table-plug-in-for-jquery/amp Basic usage
     * @see https://phppot.com/php/column-search-in-datatables-using-server-side-processing Server-side processing
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     * @param array                                 $allowedCols
     *
     * @return \Illuminate\Http\Response
     */
    public static function queryForDatatables(Builder $query, Request $request, array $allowedCols): Response
    {
        // Validate asked columns

        $params = (array) $request->query();

        $cols = array_get($params, 'columns', []);

        $askedCols = array_map(function ($col) {
            return $col['name'];
        }, $cols);

        $invalidCols = array_diff($askedCols, $allowedCols);

        if ($invalidCols) {
            return response(['message' => 'Invalid columns; '.implode(', ', $invalidCols)], 400);
        }

        // Available records

        $totalRecords = $query->count();

        // Select

        $query->select($askedCols);

        // Filter

        $isFiltered = false;

        foreach ($cols as $col) {
            if (! in_array($col['name'], $askedCols)) {
                continue;
            }

            if (! array_get($col, 'searchable')) {
                continue;
            }

            // Column filter

            if ($term = array_get($col, 'search.value')) {
                $query->orWhere($col['name'], 'ilike', "%{$term}%");
                $isFiltered = true;

                continue;
            }

            // Global filter

            if ($term = array_get($params, 'search.value')) {
                $query->orWhere($col['name'], 'ilike', "%{$term}%");
                $isFiltered = true;
            }
        }

        // Order

        foreach (array_get($params, 'order', []) as $order) {
            $colIdx = $order['column'];

            $col = $cols[$colIdx];

            if (! in_array($col['name'], $askedCols)) {
                continue;
            }

            if (! $col['orderable']) {
                continue;
            }

            $query->orderBy($col['name'], $order['dir']);
        }

        // Paginate

        $query->skip($params['start']);

        // Limit

        $query->take($params['length']);

        // Matched records

        $filteredRecords = $query->get();

        // Flatten data

        $data = $filteredRecords->map(function ($model) use ($askedCols) {
            $row = [];

            foreach ($askedCols as $col) {
                $parts = explode('.', $col);
                $field = array_pop($parts);

                if (sizeof($parts) === 2) {
                    $rel = array_pop($parts);
                    $row[$col] = $model[$rel][$field];

                    continue;
                }

                $row[$col] = $model[$field];
            }

            return $row;
        });

        return response([
            'draw' => ++$params['draw'],
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $isFiltered ? $filteredRecords->count() : $totalRecords,
            'data' => $data,
        ]);
    }
}
