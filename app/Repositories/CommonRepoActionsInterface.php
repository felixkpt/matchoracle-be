<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface CommonRepoActionsInterface
{

    function autoSave($data);

    function updateStatus($id);

    function updateStatuses(Request $request);

    function destroy($id);
}
