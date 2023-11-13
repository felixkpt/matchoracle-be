<?php

namespace App\Repositories;

use App\Models\Status;

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

    function destroy($id)
    {
        $this->model::find($id)->delete();
        return response(['message' => "Record deleted successfully."]);
    }

}
