<?php

namespace App\Repositories;

use App\Services\Filerepo\Controllers\FilesController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait CommonRepoActions
{
    protected $applyFiltersOnly;

    function autoSave($data)
    {
        $id = $data['id'] ?? request()->id;
        $data['id'] = $id;

        if (!$id) {
            $data['user_id'] = auth()->user()->id ?? 0;

            if (!isset($data['status_id'])) {
                $data['status_id'] = activeStatusId();
            }
        }

        if (isset($data['priority']) && $data['priority'] == 0) {
            $highestPriority = $this->model::max('priority') ?? 0;
            $data['priority'] = $highestPriority + 1;
        }

        $record = $this->model::updateOrCreate(['id' => $id], $data);

        $this->saveModelFiles($record);

        return $record;
    }

    function saveModelFiles($record)
    {
        try {
            // Iterate through all the files in the request
            foreach (request()->allFiles() as $field => $files) {
                Log::info('Fil:', [$record->getTable(), $field, Schema::hasColumn($record->getTable(), $field),]);

                if (Schema::hasColumn($record->getTable(), $field)) {
                    $uploader = new FilesController();
                    $file_data = $uploader->saveFiles($record, is_array($files) ? $files : [$files]);

                    // The uploader returns an array of file data
                    $path = $file_data[0]['path'] ?? null;

                    if ($path) {
                        $record->$field = $path;
                        $record->save();
                    }
                }
            }
        } catch (Exception $e) {
            Log::critical('saveModelFiles error: ' . $e->getMessage());
        }
    }

    function updateStatus($id)
    {
        request()->validate(['status_id' => 'required']);

        $status_id = request()->status_id;
        $this->model::find($id)->update(['status_id' => $status_id]);
        return response(['message' => "Status updated successfully."]);
    }

    function updateStatuses(Request $request)
    {

        $this->applyFiltersOnly = true;

        $filteredModel = method_exists($this, 'index') ? $this->index() : $this->model;

        request()->validate(['status_id' => 'required']);

        $msg = 'No record was updated.';
        $builder = $filteredModel->where('status_id', '!=', request()->status_id);

        $arr = ['status_id' => request()->status_id];
        $ids = $request->ids;

        if ($ids) {
            if ($ids == 'all') {
                $builder->update($arr);
                $msg = 'All records statuses updated.';
            } else {
                $ids = json_decode($ids);

                $builder->whereIn('id', $ids)->update($arr);
                $msg = count($ids) . ' records statuses updated.';
            }
        }

        return response(['message' => $msg]);
    }

    function destroy($id)
    {
        $this->model::find($id)->delete();
        return response(['message' => "Record deleted successfully."]);
    }
}
