<?php

namespace App\Repositories;

use App\Models\Status;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait SearchRepoTrait
{
    protected $addedColumns = [];
    protected $fillable;
    protected $addedFillable = [];
    protected $removedFillable = [];
    protected $htmls = [];
    protected $excludeFromFillables = ['user_id', 'status_id'];
    protected $statuses = [];
    protected $uri;
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
            'module_uri' => $this->moduleUri,
            'statuses' => $statuses,
            'htmls' => $this->htmls,
            'key' => Carbon::now()->unix(),
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
            'priority' => ['input' => 'input', 'type' => 'number'],
            'priority_no' => ['input' => 'input', 'type' => 'number'],

            'content*' => ['input' => 'textarea', 'type' => null, 'rows' => 7],
            'description*' => ['input' => 'textarea', 'type' => null, 'rows' => 7],

            'text' => ['input' => 'input', 'type' => 'url'],

            '*_id'  => ['input' => 'select', 'type' => null],
            '*_ids'  => ['input' => 'select', 'type' => 'multi'],
            '*_list'  => ['input' => 'select', 'type' => 'multi'],
            '*_multilist'  => ['input' => 'select', 'type' => 'multi'],
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


        // Merge guess array with added fillables
        foreach ($this->addedFillable as [$field, $before, $inputTypeInfo]) {
            $guess_array[$field] = $inputTypeInfo;
        }

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

                // Assign other properties if available
                foreach (['type', 'min', 'max', 'rows', 'accept'] as $property) {
                    if (isset($matchedType[$property])) {
                        $guessed[$field][$property] = $matchedType[$property];
                    }
                }
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

    function action($q, $options)
    {
        return (new ModelAction($this->actionItems, $q, $options))->action();
    }
}
