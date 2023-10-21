<?php

namespace App\Http\Controllers\Admin\Matches\View;

use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;

class MatchController extends Controller
{

    public function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
    ) {
    }

    function show($id)
    {
        return $this->gameRepositoryInterface->show($id);
    }
}
