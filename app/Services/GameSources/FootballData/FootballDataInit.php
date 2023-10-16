<?php

namespace App\Services\GameSources\FootballData;

use App\Repositories\FootballData;

class FootballDataInit
{

    public $api;
    public $competitions;
    public $seasons;
    public $standings;
    public $teams;

    public function __construct()
    {
        $this->api = new FootballData();
        $this->competitions = new Competitions();
        $this->seasons = new Seasons();
        $this->standings = new Standings();
        $this->teams = new Teams();
    }
}
