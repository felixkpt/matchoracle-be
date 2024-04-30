<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The SearchRepo class provides functionality for searching and sorting data using Laravel's Eloquent ORM or Query Builder.
 *
 * This class was contributed by:
 *   - Ian Kibet (https://gitlab.com/ictianspecialist)
 *   - Felix (https://github.com/felixkpt)
 *
 * Created Date: 7/12/17
 * Updated: July 1, 2023.
 * License: MIT
 */
class SearchRepo
{

    use SearchRepoTrait;

    protected $builder;

    protected $addedColumns = [];
    protected $model;
    protected $model_name = '';
    protected $moduleUri;
    protected $fillable;
    protected $addedFillable = [];
    protected $removedFillable = [];
    protected $htmls = [];
    protected $excludeFromFillables = ['user_id', 'status_id'];
    protected $statuses = [];
    protected $request_data;

    protected $actionItems = [
        [
            'title' => 'View',
            'action' => ['title' => 'view', 'modal' => 'view', 'native' => 'navigate', 'use' => 'modal']
        ],
        [
            'title' => 'Edit',
            'action' => ['title' => 'edit', 'modal' => 'edit', 'native' => 'edit', 'use' => 'modal']
        ],
        [
            'title' => 'Update status',
            'action' => ['title' => 'update-status', 'modal' => 'update-status', 'native' => null, 'use' => 'modal']
        ]
    ];

    /**
     * Create a new instance of SearchRepo.
     *
     * @param mixed $builder The builder instance (EloquentBuilder or QueryBuilder).
     * @param array $searchable The columns to search against.
     * @param callable|null $search_builder A callback function to customize the search query (optional).
     * @return SearchRepo The SearchRepo instance.
     */
    public static function of($builder, $searchable = [], $search_builder = null)
    {

        $self = new self;
        $self->builder = $builder;
        $self->request_data = request()->all();

        $model = null;
        if (method_exists($builder, 'getModel')) {
            $model = $builder->getModel();
            $self->model = $model;

            $currentConnection = DB::getDefaultConnection();
            $self->model_name = str_replace('_', ' ', Str::after(class_basename(get_class($model)), $currentConnection . '_'));

            $searchable = $searchable ?: $model->searchable;
        }

        $model_table = $model->getTable();

        $request_data = request()->all();
        $self->request_data = $request_data;


        // Handle searching logic
        $term = $request_data['search'] ?? null;

        $search_field = $request_data['search_field'] ?? null;

        // Define an array to map fields to search strategies
        $searchStrategyArray = [
            'is_fcr' => 'equals',
        ];

        // Determine the search strategy based on the search field
        $strategy = $searchStrategyArray[$search_field] ?? 'like'; // Default strategy is 'like'

        // Special case: Handle fields ending with '_id' for exact matching
        if ($strategy === 'like') {

            // Check if the search term is enclosed in quotation marks
            if (preg_match('/^"(.*)"$/i', $term, $matches) || preg_match('/^\'(.*)\'$/i', $term, $matches)) {
                $term = $matches[1]; // Strip the quotation marks
                $strategy = 'equals';
            }
        }

        if ($search_field) {
            $searchable = [$search_field];
        }

        if (!empty($term) && !empty($searchable)) {

            if ($builder instanceof EloquentBuilder) {

                $builder = $builder->where(function ($q) use ($searchable, $term, $model_table, $strategy, $search_builder) {

                    if (is_callable($search_builder)) {
                        return $q->where($search_builder);
                    }

                    foreach ($searchable as $column) {
                        // Log::info('Searching:', ['term' => $term, 'model_table' => $model_table, 'col' => $column]);

                        if (Str::contains($column, '.')) {

                            [$relation, $column] = explode('.', $column, 2);

                            $relation = Str::camel($relation);

                            // Apply search condition within the relation
                            $q->orWhereHas($relation, function (EloquentBuilder $query) use ($column, $term, $strategy) {
                                $query->where($column, $strategy === 'like' ? 'like' : '=', $strategy === 'like' ? "%$term%" : "$term");
                            });
                        } else {
                            // Apply search condition on the main table
                            $q->orWhere($model_table . '.' . $column, $strategy === 'like' ? 'like' : '=', $strategy === 'like' ? "%$term%" : "$term");

                            Log::critical("Search results:", ['tbl' => $model_table . '.' . $column, 'res' => $q->first()]);
                        }
                    }
                });
            } elseif ($builder instanceof QueryBuilder) {

                if (is_callable($search_builder)) {
                    $builder->where($search_builder);
                } else {
                    foreach ($searchable as $column) {
                        if (Str::contains($column, '.')) {
                            [$relation, $column] = explode('.', $column, 2);

                            $relation = Str::camel($relation);

                            $builder->orWhere(function (QueryBuilder $query) use ($column, $term, $strategy) {
                                $query->where($column, $strategy === 'like' ? 'like' : '=', $strategy === 'like' ? "%$term%" : "$term");
                            });
                        } else {
                            // Apply search condition on the main table
                            $builder->orwhere($model_table . '.' . $column, $strategy === 'like' ? 'like' : '=', $strategy === 'like' ? "%$term%" : "$term");
                        }
                    }
                }
            }
        }

        return $self;
    }

    /**
     * Perform sorting of the search results.
     */
    function sort()
    {

        $builder = $this->builder;

        $model_table = $this->model->getTable();

        $builder = $this->orders($builder, $model_table);
    }

    function orders($builder, $model_table)
    {

        if (request()->order_by) {
            $orderBy = Str::lower(request()->order_by);

            if (Str::contains($orderBy, '.')) {
                [$relation, $column] = explode('.', $orderBy, 2);

                $possibleRelation = Str::camel($relation);

                if ($this->model && method_exists($this->model, $possibleRelation)) {

                    $orderBy = $relation . '_id';
                    if (Schema::hasColumn($model_table, $orderBy)) {
                        $order_direction = request()->order_direction ?? 'asc';
                        $builder->orderBy($orderBy, $order_direction);
                    }
                }
            } elseif ($this->model && Schema::hasColumn($model_table, $orderBy)) {
                $order_direction = request()->order_direction ?? 'asc';
                $builder->orderBy($orderBy, $order_direction);
            }
        } else {
            $builder->orderBy($model_table . '.id', 'desc');
        }

        return $builder;
    }

    /**
     * Add an order by clause to the query builder.
     *
     * @param string $column The column to order by.
     * @return $this The SearchRepo instance.
     */
    function orderBy($column, $direction = 'asc')
    {
        $this->builder = $this->builder->orderBy($column, $direction);

        return $this;
    }

    /**
     * Add a custom column to the search results.
     *
     * @param string $column The column name.
     * @param \Closure $callback The callback function to generate the column value.
     * @return $this The SearchRepo instance.
     */
    public function addColumn($column, $callback)
    {
        $this->addedColumns[$column] = $callback;

        return $this;
    }

    /**
     * Add a custom column to the search results if the given "value" is (or resolves to) truthy.
     *
     * @param string $condition The condition to be checked if true.
     * @param string $column The column name.
     * @param \Closure $callback The callback function to generate the column value.
     * @return $this The SearchRepo instance.
     */
    public function addColumnWhen($condition, $column, $callback)
    {
        if ($condition) {
            $this->addColumn($column, $callback);
        }

        return $this;
    }

    /**
     * Add a custom column to the search results.
     *
     * @param string $column The column name.
     * @param \Closure $callback The callback function to generate the column value.
     * @return $this The SearchRepo instance.
     */
    public function addActionColumn($column, $uri, $options = [])
    {
        if (!$this->moduleUri) {
            $this->moduleUri = $uri;
        }

        $this->addedColumns[$column] = ['method' => 'action', 'options' => array_merge($options, ['uri' => $uri])];

        return $this;
    }
    /**
     * Add an action column to the search results conditionally.
     *
     * @param bool $condition The condition to determine whether to add the action column.
     * @param string $column The column name.
     * @param string $uri The URI for the action column.
     * @param string $view The view parameter for the action column.
     * @param string $edit The edit parameter for the action column.
     * @param string|null $hide The hide parameter for the action column.
     * @return $this The SearchRepo instance.
     */
    public function addActionColumnWhen($condition, $column, $uri, $options = [])
    {
        if ($condition) {
            $this->addActionColumn($column, $uri, $options);
        }

        return $this;
    }

    /**
     * Paginate the search results.
     *
     * @param int $perPage The number of items per page.
     * @param array $columns The columns to retrieve.
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated results.
     */
    function paginate($perPage = null, $columns = ['*'])
    {

        $this->sort();

        $builder = $this->builder;

        $perPage = ($perPage ?? request()->per_page) ?? 50;
        $page = request()->page ?? 1;

        // Handle last page results
        $results = $builder->paginate($perPage, $columns, 'page', $page);

        $currentPage = $results->currentPage();
        $lastPage = $results->lastPage();
        $items = $results->items();

        if ($currentPage > $lastPage && count($items) === 0) {
            $results = $builder->paginate($perPage, $columns, 'page', $lastPage);
        }

        $r = $this->additionalColumns($results);

        $results->setCollection(collect($r));

        $custom = collect($this->getCustoms());

        // Append all request data to the pagination links
        $results->appends($this->request_data);

        // for api consumption remove the following line
        // $pagination = $results->links()->__toString();

        // maintain the following line for api consumption
        $results = $custom->merge($results);

        // for api consumption remove the following line
        // $results['pagination'] = $pagination;

        return $results;
    }

    /**
     * Get the search results without pagination.
     *
     * @param array $columns The columns to retrieve.
     * @return array The search results.
     */
    function get($perPage = null, $columns = ['*'])
    {
        $this->sort();
        $builder =  $this->builder;

        $perPage = ($perPage ?? request()->per_page) ?? 50;

        $results = $builder->limit($perPage)->get($columns);

        $r = $this->additionalColumns($results);

        $results = ['data' => $r];

        $custom = collect($this->getCustoms());

        $results = $custom->merge($results);

        return $results;
    }

    /**
     * Get the first search result without pagination.
     *
     * @param array $columns The columns to retrieve.
     * @return array The search result.
     */
    function first($columns = ['*'])
    {
        // Retrieve the first result without pagination
        $result = $this->builder->first($columns);

        if ($result) {
            $item = $result;

            // Loop through added custom columns and add them to the stdClass object
            foreach ($this->addedColumns as $column => $callback) {
                if (is_array($callback) && isset($callback['method'])) {
                    // If the callback is an array with a 'method', call the method with options
                    $item->$column = $this->action($item, $callback['options']);
                } else {
                    // If the callback is a closure, call the closure
                    $item->$column = $callback($item);
                }
            }
        }

        // Create an array with a 'data' key containing the result
        $results = ['data' => $result];

        // Merge in custom data obtained from the 'getCustoms' method
        $custom = collect($this->getCustoms());
        $results = $custom->merge($results);

        return $results;
    }

    /**
     * Get the first search result without pagination or throw an exception if not found.
     *
     * @param array $columns The columns to retrieve.
     * @return array The search result.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if no result is found.
     */
    function firstOrFail($columns = ['*'])
    {
        // Reuse the existing first method to get the result
        $result = $this->first($columns);

        if (!$result['data']) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('No results found', 404);
        }

        return $result;
    }

    /**
     * Add additional custom columns to the search results.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $results The paginated results.
     * @return array The search results with additional columns.
     */
    function additionalColumns($results)
    {
        $data = method_exists($results, 'items') ? $results->items() : $results;

        foreach ($data as $item) {

            foreach ($this->addedColumns as $column => $callback) {

                if (is_array($callback) && isset($callback['method'])) {
                    $item->$column = $this->action($item, $callback['options']);
                } else
                    $item->$column = $callback($item);
            }
        }

        return $data;
    }

    /**
     * Specify fillable columns for the search results.
     *
     * @param array $fillable The fillable columns.
     * @return $this The SearchRepo instance.
     */
    public function fillable($fillable = [])
    {
        if (is_array($fillable))
            $this->fillable = $fillable;

        return $this;
    }

    /**
     * Add a custom column to the search results.
     *
     * @param string $fillable The column name.
     * @param string $before The after function to generate the column value.
     * @param array $inputTypeInfo The input info. eg ->addFillable('skill_ids', 'priority', ['input' => 'multiselect', 'type' => null])
     */
    public function addFillable($field, $before = null, $inputTypeInfo = [])
    {
        $this->addedFillable[] = [$field, $before, $inputTypeInfo];

        return $this;
    }

    /**
     * Add a custom column to the search results.
     *
     * @param string $fillable The column name.
     */
    public function removeFillable($fields = [])
    {
        $this->removedFillable = $fields;

        return $this;
    }
}
