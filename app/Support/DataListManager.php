<?php


namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Closure;
use Yajra\DataTables\Facades\DataTables;

class DataListManager
{
    public static function list(
        Request $request,
        Builder $query,
        array $searchable = [],
        array $filters = [],
        array $select = [],
        array $notIn = [],
        ?Closure $joinCallback = null,
        array $config = []
    ): array {

        $settingPerPage = $config['per_page'] ?? 20;
        $page     = (int) $request->get('page', 1);
        $perPage  = (int) $request->get('per_page', $settingPerPage);
        $orderBy  = $request->get('orderBy', 'desc');
        $orderColumn   = $request->get('orderColumn', 'id');
        $search   = $request->get('search');
        $listSize = $request->get('list_size');

        /** ---------------- JOIN ---------------- */
        if ($joinCallback) {
            $joinCallback($query);
        }
        /** ---------------- NOT IN ---------------- */
        foreach ($notIn as $column => $values) {
            if (!empty($values) && is_array($values)) {
                $query->whereNotIn($column, $values);
            }
        }

        /** ---------------- SELECT ---------------- */
        if (!empty($select)) {
            $query->select($select);
        }
        // if empty → select * (default behavior)

        /** ---------------- FILTERS ---------------- */
        foreach ($filters as $key => $filter) {

            // SIMPLE key => column fallback
            if (is_string($filter)) {
                if ($request->filled($key)) {
                    $query->where($filter, $request->get($key));
                }
                continue;
            }

            $column = $filter['column'] ?? null;
            $type   = $filter['type'] ?? 'basic';

            if (!$column) {
                continue;
            }

            // BASIC (=)
            if ($type === 'basic' && $request->filled($key)) {
                $query->where($column, $request->get($key));
            }

            // DATE
            if ($type === 'date' && $request->filled($key)) {
                $query->whereDate($column, $request->get($key));
            }

            // DATE RANGE
            if ($type === 'daterange') {
                $from = $request->get($key . '_from');
                $to   = $request->get($key . '_to');

                if ($from && $to) {
                    $query->whereBetween($column, [
                        $from . ' 00:00:00',
                        $to . ' 23:59:59',
                    ]);
                }
            }
        }

        /** ---------------- SEARCH ---------------- */
        if ($search) {
            if (is_array($search)) {
                $search = $request->input('search.value');
            }

            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        /** ---------------- COUNT ---------------- */
        $totalCount = (clone $query)->count();
        $totalPages = ceil($totalCount / $perPage);

        /** ---------------- DATA ---------------- */
        if ($listSize === 'web') {
            $items = $query->orderBy($orderColumn, $orderBy)
                ->paginate($settingPerPage);
        } elseif ($listSize === 'all') {
            $items = $query->orderBy($orderColumn, $orderBy)->get();
        } elseif ($listSize === 'datatable') {
            $items = $query->orderBy($orderColumn, $orderBy);
        } else {
            $items = $query->orderBy($orderColumn, $orderBy)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
        }

        return [
            'total_count'  => $totalCount,
            'total_page'   => $totalPages,
            'per_page'     => $perPage,
            'current_page' => $page,
            'data'         => $items,
        ];

    }

    public static function dataTableHandle(
        Request $request,
        callable $dataProvider,
        array $columns = [],
        array $rawColumns = []
    ) {
        $request->merge(['list_size' => 'datatable']);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = call_user_func($dataProvider, $request);

        $dataTable = DataTables::eloquent($query)
        ->addIndexColumn();
        /** ---------------- Columns ---------------- */
        foreach ($columns as $name => $callback) {
            if ($callback instanceof Closure) {
                $dataTable->addColumn($name, $callback);
            }
        }

        if (!empty($rawColumns)) {
            $dataTable->rawColumns($rawColumns);
        }

        return $dataTable->make(true);
    }
}
