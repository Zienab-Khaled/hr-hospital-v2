<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasIndexFilters
{
    /**
     * Apply search and filters to query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchable - Array of columns to search in
     * @param array $filters - Array of [query_param => column_name]
     * @param array $relationFilters - Array of [query_param => [relation, column]]
     * @return Builder
     */
    protected function applyIndexFilters(
        Builder $query,
        Request $request,
        array $searchable = [],
        array $filters = [],
        array $relationFilters = []
    ): Builder {
        // Apply search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    // Check if it's a relation search (e.g., 'patient.name')
                    if (strpos($column, '.') !== false) {
                        [$relation, $relationColumn] = explode('.', $column, 2);
                        $q->orWhereHas($relation, function ($relQuery) use ($relationColumn, $search) {
                            $relQuery->where($relationColumn, 'like', "%{$search}%");
                        });
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        // Apply direct column filters
        foreach ($filters as $key => $column) {
            if ($value = $request->get($key)) {
                $query->where($column, $value);
            }
        }

        // Apply relation filters
        foreach ($relationFilters as $key => $config) {
            if ($value = $request->get($key)) {
                [$relation, $column] = $config;
                $query->whereHas($relation, function ($q) use ($column, $value) {
                    $q->where($column, $value);
                });
            }
        }

        return $query;
    }

    /**
     * Get pagination size from request
     *
     * @param Request $request
     * @param int $default
     * @return int
     */
    protected function getPerPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->get('per_page', $default);
        return in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : $default;
    }
}
