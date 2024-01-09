<?php

namespace App\Http\Controllers\Admin\Matches\View;

use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\Team\TeamRepositoryInterface;
use App\Services\Validations\Game\GameValidationInterface;

class MatchController extends Controller
{

    public function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
        private TeamRepositoryInterface $teamRepositoryInterface,
        private GameValidationInterface $gameValidationInterface
    ) {
    }

    function show($id)
    {
        return $this->gameRepositoryInterface->show($id);
    }

    function head2head($id)
    {
        return $this->teamRepositoryInterface->head2head($id);
    }

    function vote($id)
    {
        $data = $this->gameValidationInterface->vote($id);

        return $this->gameRepositoryInterface->vote($id, $data);
    }

    function updateGame($id)
    {
        return $this->gameRepositoryInterface->updateGame($id);
    }
}
