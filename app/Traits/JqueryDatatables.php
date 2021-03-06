<?php

namespace App\Traits;

use App\Support\Datatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

trait JqueryDatatables
{
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
     * @param array                                 $tableModelMap
     *
     * @return \Illuminate\Http\Response
     */
    public static function queryForDatatables(Builder $query, array $constraints, array $tableModelMap): Response
    {
        $isFiltered = false;

        $availableRecords = $query->count();

        $select = Datatable::prefixFields($constraints['select']);

        $query->select($select);

        // ..........

        if ($filters = array_get($constraints, 'where')) {
            $isFiltered = true;
            $query = Datatable::applyFilters($query, $filters);
        }

        if ($filterGroups = array_get($constraints, 'where-group')) {
            $isFiltered = true;
            $query = Datatable::applyGroupedFilters($query, $filterGroups);
        }

        // ..........

        if ($orderBy = array_get($constraints, 'order-by')) {
            foreach ($orderBy as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        $query->skip($constraints['offset']);

        $query->take($constraints['limit']);

        // $query->dump();

        $matchedRecords = $query->get();

        $data = Datatable::sqlToModel($matchedRecords->toArray(), $tableModelMap);

        return response([
            'draw' => ++$constraints['draw'],
            'recordsTotal' => $availableRecords,
            'recordsFiltered' => $isFiltered ? $matchedRecords->count() : $availableRecords,
            'data' => $data,
        ]);
    }
}
