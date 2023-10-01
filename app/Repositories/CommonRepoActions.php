<?php

namespace App\Repositories;

trait CommonRepoActions
{

    function statusUpdate($id)
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
