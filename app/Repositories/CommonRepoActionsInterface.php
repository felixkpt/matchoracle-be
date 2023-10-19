<?php

namespace App\Repositories;

interface CommonRepoActionsInterface
{

    function autoSave($data);

    function updateStatus($id);

    function destroy($id);

}
