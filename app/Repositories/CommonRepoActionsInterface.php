<?php

namespace App\Repositories;

interface CommonRepoActionsInterface
{

    function autoSave($data);

    function statusUpdate($id);

    function destroy($id);

}
