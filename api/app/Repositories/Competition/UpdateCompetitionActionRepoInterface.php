<?php

namespace  App\Repositories\Competition;

use App\Repositories\CommonRepoActionsInterface;

interface UpdateCompetitionActionRepoInterface extends CommonRepoActionsInterface
{
    function updateAction($id, $action);
}
