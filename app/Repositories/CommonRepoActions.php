<?php

namespace App\Repositories;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait CommonRepoActions
{
    protected $applyFiltersOnly;

    function autoSave($data)
    {
        $id = $data['id'] ?? request()->id;
        $data['id'] = $id;

        if (!$id) {
            $data['user_id'] = auth()->user()->id;

            if (!isset($data['status_id'])) {
                $data['status_id'] = activeStatusId();
            }
        }

        $record = $this->model::updateOrCreate(['id' => $id], $data);
        return $record;
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
        sleep(4);

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
