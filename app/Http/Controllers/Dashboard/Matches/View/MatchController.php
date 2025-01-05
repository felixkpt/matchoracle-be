<?php

namespace App\Http\Controllers\Dashboard\Matches\View;

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
    ) {}

    public function show($id)
    {
        return $this->gameRepositoryInterface->show($id);
    }

    public function combinedMatches($id, $home_team_id, $away_team_id)
    {
        return $this->teamRepositoryInterface->combinedMatches($home_team_id, $away_team_id);
    }

    public function head2head($id)
    {
        return $this->teamRepositoryInterface->head2head($id);
    }

    public function vote($id)
    {
        $data = $this->gameValidationInterface->vote($id);

        return $this->gameRepositoryInterface->vote($id, $data);
    }

    public function updateGame($id)
    {
        return $this->gameRepositoryInterface->updateGame($id);
    }
}
