<?php

namespace App\Repositories\SearchRepo;

class ModelAction
{
    protected $actionItems;
    protected $record;
    protected $options;
    function __construct($actionItems, $record, $options)
    {
        $this->actionItems = $actionItems;
        $this->record = $record;
        $this->options = $options;
    }

    function action()
    {

        $parent_uri = $this->options['uri'];
        $uri = $this->options['uri'] . 'view/{id}';

        $method = $this->options['method'] ?? 'psst';

        $is_custom = false;
        if (isset($this->options['create_uri']) && $this->options['create_uri']) {
            $is_custom = true;
            $uri = $this->options['create_uri'];
        }

        $view = $options['view'] ?? 'modal';
        $edit = $options['edit'] ?? 'modal';

        $uri = preg_replace('#/+#', '/', $uri . '/');

        $str = '';
        foreach ($this->actionItems as $item) {
            // get method 
            if ($item['action']['title'] === 'view') {
                $resolvedUri = $uri . 'view/' . $this->record->id . '/';
                if ($is_custom) {
                    $resolvedUri = $parent_uri . 'view/' . $this->record->id;
                }

                if (checkPermission($uri, 'get')) {
                    $use = $view === 'native' ? $item['action']['title'] : $item['action']['use'];
                    $str .= '<li><a class="dropdown-item autotable-'
                        . ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native'])
                        . '" data-id="' . $this->record->id . '" href="' . $resolvedUri . '">'
                        . $item['title']
                        . '</a></li>';
                }
            }
            // put method 
            else if ($item['action']['title'] === 'edit') {
                $resolvedUri = preg_replace('#\{.*\}#', $this->record->id, $uri, 1);

                if (checkPermission($uri, 'put')) {
                    $use = $edit === 'native' ? $item['action']['title'] : $item['action']['use'];
                    $str .= '<li><a class="dropdown-item autotable-' .
                        ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native'])
                        . '" data-id="' . $this->record->id . '" href="' . $resolvedUri . '">'
                        . $item['title']
                        . '</a></li>';
                }
            }
            // assumed post method 
            else {
                $resolvedUri = $uri . 'view/' . $this->record->id . '/#action';

                if (checkPermission($uri, $method)) {
                    $use = $item['action']['use'];
                    $str .=
                        '<li><a class="dropdown-item autotable-' .
                        ($use === 'modal' ? 'modal-' . $item['action']['modal'] : $item['action']['native'])
                        . '" data-id="' . $this->record->id . '" href="' . $resolvedUri . $item['action']['title'] . '">'
                        . $item['title']
                        . '</a></li>';
                }
            }
        }

        return strlen($str) ? $this->dropdown($str) : '-';
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
