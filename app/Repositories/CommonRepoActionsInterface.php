<?php

namespace App\Repositories;

interface CommonRepoActionsInterface
{

    function statusUpdate($id);

    function destroy($id);
}
