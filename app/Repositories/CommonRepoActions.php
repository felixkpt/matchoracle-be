<?php

namespace App\Repositories;

use App\Models\Status;
use Illuminate\Http\Request;

trait CommonRepoActions
{

    function autoSave($data)
    {

        $id = $data['id'] ?? request()->id;
        $data['id'] = $id;

        if (!$id) {
            $data['user_id'] = auth()->user()->id;

            if (!isset($data['status_id'])) {
                $data['status_id'] = Status::wherename('active')->first()->id ?? 0;
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
        sleep(6);
        
        request()->validate(['status_id' => 'required']);

        $msg = 'No record was updated.';
        $builder = $this->model::query()->where('status_id', '!=', request()->status_id);
        
        $arr = ['status_id' => request()->status_id];
        $ids = $request->ids;
        if (is_array($ids) && count($ids) > 0) {
            $builder->whereIn('id', $ids)->update($arr);
            $msg = count($ids) . ' record statuses updated.';
        } else if ($ids == 'all') {
            $builder->update($arr);
            $msg = 'All records statuses updated.';
        }

        return response(['message' => $msg]);
    }

    function destroy($id)
    {
        $this->model::find($id)->delete();
        return response(['message' => "Record deleted successfully."]);
    }
}
