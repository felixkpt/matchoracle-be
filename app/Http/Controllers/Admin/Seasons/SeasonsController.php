<?php

namespace App\Http\Controllers\Admin\Seasons;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Season\SeasonRepositoryInterface;
use Illuminate\Http\Request;

class SeasonsController extends Controller
{

    use CommonControllerMethods;

    function __construct(
        private SeasonRepositoryInterface $seasonRepositoryInterface,
    ) {
        $this->repo = $seasonRepositoryInterface;
    }

    function index()
    {
        return $this->seasonRepositoryInterface->index();
    }

    function store(Request $request)
    {
        $data = [];
        return $this->seasonRepositoryInterface->store($request, $data);
    }
}
