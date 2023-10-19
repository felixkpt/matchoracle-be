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
        $this->competitions = new Competitions($this->api);
        $this->seasons = new Seasons($this->api);
        $this->standings = new Standings($this->api);
        $this->teams = new Teams($this->api);
    }

    public function competitions()
    {
        return new Competitions($this->api);
    }

    public function seasons()
    {
        return new Seasons($this->api);
    }

    public function standings()
    {
        return new Standings($this->api);
    }

    public function teams()
    {
        return new Teams($this->api);
    }
}
