<?php

namespace App\Http\Controllers\Admin\Teams\Coaches;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Coach\CoachRepositoryInterface;
use App\Services\Validations\Team\Coach\CoachValidationInterface;
use Illuminate\Http\Request;

class CoachesController extends Controller
{
    use CommonMethods;

    function __construct(
        private CoachRepositoryInterface $coachRepositoryInterface,
        private CoachValidationInterface $coachValidationInterface,
    ) {
        $this->repo = $coachRepositoryInterface;
    }

    function index()
    {
        return $this->coachRepositoryInterface->index();
    }

    function store(Request $request)
    {

        $data = $this->coachValidationInterface->store();

        return $this->coachRepositoryInterface->store($request, $data);
    }
}
