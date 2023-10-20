<?php

namespace App\Http\Controllers\Admin\Seasons\View;

use App\Http\Controllers\Controller;
use App\Repositories\Season\SeasonRepositoryInterface;

class SeasonController extends Controller
{

    public function __construct(
        private SeasonRepositoryInterface $competitionRepositoryInterface,
    ) {
    }
}
