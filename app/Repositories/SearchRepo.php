<?php

namespace App\Repositories;

use App\Models\Status;
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
    protected $builder;

    protected $addedColumns = [];
    protected $model;
    protected $model_name = '';
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
     * @return SearchRepo The SearchRepo instance.
     */
    public static function of($builder, $searchable = [])
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
        $term = $request_data['q'] ?? null;

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

                $builder = $builder->where(function ($q) use ($searchable, $term, $model_table, $strategy) {

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
    public function addActionColumn($column, $uri, $view = 'modal', $edit = 'modal', $hide = null)
    {
        $this->addedColumns[$column] = ['method' => 'action', 'parameters' => [$uri, $view, $edit, $hide]];

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
    public function addActionColumnWhen($condition, $column, $uri, $view = 'modal', $edit = 'modal', $hide = null)
    {
        if ($condition) {
            $this->addActionColumn($column, $uri, $view, $edit, $hide);
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
                    // If the callback is an array with a 'method', call the method with parameters
                    $item->$column = $this->action($item, ...$callback['parameters']);
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

                    $item->$column = $this->action($item, ...$callback['parameters']);
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
     * @param array $inputTypeInfo The input info.
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

    /**
     * Specify htmls columns for the search results.
     *
     * @param array $htmls The htmls columns.
     * @return $this The SearchRepo instance.
     */
    public function htmls($htmls = [])
    {
        if (is_array($htmls))
            $this->htmls = $htmls;

        return $this;
    }

    /**
     * Get an array of custom data to include in the search results.
     *
     * @return array An array of custom data.
     */
    function getCustoms()
    {

        // user supplied fillable
        $fillable = $this->fillable;

        // model fillable
        if (!is_array($fillable)  && $this->model) {
            $fill = $this->model->getFillable(true);
            $fill = array_diff($fill, $this->excludeFromFillables);
            $fillable = array_values($fill);
        }

        $fillable = $this->mergeFillable($fillable);

        $statuses = $this->statuses ?: Status::select('id', 'name', 'icon', 'class')->get()->toArray();

        $arr = [
            'fillable' => $this->getFillable($fillable),
            'model_name' => $this->model_name, 'model_name_plural' => Str::plural($this->model_name),
            'statuses' => $statuses,
            'htmls' => $this->htmls,
        ];
        return $arr;
    }

    public function mergeFillable($fillable)
    {
        $fillable = is_array($fillable) ? $fillable : [];

        $fillable = array_filter($fillable, fn ($item) => !in_array($item, $this->removedFillable));

        $addedFillable = $this->addedFillable;

        if (!empty($addedFillable)) {
            foreach ($addedFillable as $fill) {
                [$field, $before] = $fill;

                if (!$before) {
                    // Merge the new columns with the existing ones.
                    array_push($fillable, $field);
                } else {
                    // Find the position of the specified value in the existing fillable array.
                    $beforeValIndex = array_search($before, $fillable);

                    if ($beforeValIndex !== false) {
                        if ($beforeValIndex === 0) {
                            array_unshift($fillable, $field);
                            $fillable = array_values($fillable);
                        } else {

                            // Split the array into two parts.
                            $before = array_values(array_slice($fillable, 0, $beforeValIndex));

                            array_push($before, $field);

                            $after = array_values(array_slice($fillable, $beforeValIndex));

                            // Merge the new columns in between.
                            $fillable = array_merge($before, $after);
                        }
                    } else {
                        // If the value is not found, perform normal array push.
                        array_push($fillable, $field);
                    }
                }
            }
        }

        return array_values($fillable);
    }


    /**
     * Get an array of guessed input types for fillable columns.
     *
     * @param array $fillable The fillable column names.
     * @return array An array of guessed input types.
     */
    function getFillable(array $fillable)
    {
        $guess_array = [
            'name' => ['input' => 'input', 'type' => 'name'],
            'email' => ['input' => 'input', 'type' => 'email'],
            'password' => ['input' => 'input', 'type' => 'password'],
            'password_confirmation' => ['input' => 'input', 'type' => 'password'],
            'priority_number' => ['input' => 'input', 'type' => 'number'],
            'priority_no' => ['input' => 'input', 'type' => 'number'],

            'content*' => ['input' => 'textarea', 'type' => null, 'rows' => 10],
            'description*' => ['input' => 'textarea', 'type' => null], 'rows' => 10,

            'text' => ['input' => 'input', 'type' => 'url'],

            '*_id'  => ['input' => 'select', 'type' => null],
            '*_ids'  => ['input' => 'multiselect', 'type' => null],
            '*_list'  => ['input' => 'select', 'type' => null],
            '*_multilist'  => ['input' => 'multiselect', 'type' => null],
            'guard_name' => ['input' => 'select', 'type' => null],

            'img' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],
            'image' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],
            'emblem' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],
            'logo' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],
            'flag' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],
            'avatar' => ['input' => 'input', 'type' => 'file', 'accept' => 'image/*'],

            '*_time' => ['input' => 'input', 'type' => 'datetime-local'],
            '*_name' => ['input' => 'input', 'type' => 'name'],
            'last_*' => ['input' => 'input', 'type' => 'datetime-local'],
            '*_at' => ['input' => 'input', 'type' => 'datetime-local'],

            '*date' => ['input' => 'input', 'type' => 'date'],

            'is_*' => ['input' => 'input', 'type' => 'checkbox'],
        ];

        $guessed = [];

        foreach ($fillable as $field) {
            $matchedType = null;

            foreach ($guess_array as $pattern => $type) {
                if (fnmatch($pattern, $field)) {
                    $matchedType = $type;
                    break;
                }
            }

            if ($matchedType) {
                $guessed[$field]['input'] = $matchedType['input'];

                if (isset($matchedType['type']))
                    $guessed[$field]['type'] = $matchedType['type'];

                if (isset($matchedType['min']))
                    $guessed[$field]['min'] = $matchedType['min'];

                if (isset($matchedType['max']))
                    $guessed[$field]['max'] = $matchedType['max'];

                if (isset($matchedType['rows']))
                    $guessed[$field]['rows'] = $matchedType['rows'];

                if (isset($matchedType['accept']))
                    $guessed[$field]['accept'] = $matchedType['accept'];
            } else {
                $guessed[$field] = ['input' => 'input', 'type' => 'text'];
            }
        }

        // addedfillabes here

        foreach ($this->addedFillable as $fill) {

            [$field, $before, $inputTypeInfo] = $fill;

            if (is_array($inputTypeInfo) && count($inputTypeInfo) > 0) {
                $guess_array[$field] = $matchedType;
            }

            $matchedType = $inputTypeInfo;

            if ($matchedType) {
                $guessed[$field]['input'] = $matchedType['input'];

                if (isset($matchedType['type']))
                    $guessed[$field]['type'] = $matchedType['type'];
                else
                    unset($guessed[$field]['type']);

                if (isset($matchedType['capitalize']))
                    $guessed[$field]['capitalize'] = $matchedType['capitalize'];

                if (isset($matchedType['source']))
                    $guessed[$field]['source'] = $matchedType['source'];

                if (isset($matchedType['min']))
                    $guessed[$field]['min'] = $matchedType['min'];

                if (isset($matchedType['max']))
                    $guessed[$field]['max'] = $matchedType['max'];

                if (isset($matchedType['rows']))
                    $guessed[$field]['rows'] = $matchedType['rows'];

                if (isset($matchedType['accept']))
                    $guessed[$field]['accept'] = $matchedType['accept'];
            } else {
                $guessed[$field] = ['input' => 'input', 'type' => 'text'];
            }
        }

        return $guessed;
    }

    /**
     * Specify custom statuses for the search results.
     *
     * @param mixed $statuses The custom statuses to include.
     * @return $this The SearchRepo instance.
     */
    public function statuses($statuses)
    {
        if (!empty($statuses)) {
            $this->statuses = $statuses;
        }

        return $this;
    }

    function addActionItem($newItem, $before)
    {
        if ($before !== null) {
            // Find the index of the item to insert before
            $index = array_search(strtolower($before), array_map(fn ($item) => strtolower($item), array_column($this->actionItems, 'title')));

            if ($index !== false) {
                // Insert the new item before the specified item
                array_splice($this->actionItems, $index, 0, [$newItem]);
            } else {
                // If the specified item is not found, add the new item at the start
                array_push($this->actionItems, $newItem);
            }
        } else {
            // If no specific item is specified, add the new item at the end
            array_push($this->actionItems, $newItem);
        }

        return $this;
    }

    function action($q, $uri, $view = 'modal', $edit = 'modal')
    {

        $uri = preg_replace('#/+#', '/', $uri . '/');

        $str = '';
        foreach ($this->actionItems as $item) {
            if ($item['action']['title'] === 'view') {
                $use = $view === 'native' ? $item['action']['title'] : $item['action']['use'];
                $str .= '<li><a class="dropdown-item autotable-' . ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native']) . '" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . $item['title'] . '</a></li>';
            } else if ($item['action']['title'] === 'edit') {
                $use = $edit === 'native' ? $item['action']['title'] : $item['action']['use'];
                $str .= '<li><a class="dropdown-item autotable-' . ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native']) . '" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '/' . $item['action']['title'] . '">' . $item['title'] . '</a></li>';
            } else {
                $use = $item['action']['use'];
                $str .= '<li><a class="dropdown-item autotable-' . ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native']) . '" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '/' . $item['action']['title'] . '">' . $item['title'] . '</a></li>';
            }
        }

        return $this->dropdown($str);
    }

    private function dropdown($str)
    {
        return '
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="icon icon-list2 font-20"></i>
            </button>
            <ul class="dropdown-menu">
                ' . $str . '
            </ul>
        </div>
        ';
    }
}
