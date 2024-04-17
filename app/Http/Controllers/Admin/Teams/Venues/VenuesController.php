<?php

namespace App\Http\Controllers\Admin\Teams\Venues;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Venue\VenueRepositoryInterface;
use App\Services\Validations\Team\Venue\VenueValidationInterface;
use Illuminate\Http\Request;

class VenuesController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private VenueRepositoryInterface $venueRepositoryInterface,
        private VenueValidationInterface $venueValidationInterface,
    ) {
        $this->repo = $venueRepositoryInterface;
    }

    function index()
    {
        return $this->venueRepositoryInterface->index();
    }

    function store(Request $request)
    {

        $data = $this->venueValidationInterface->store();

        return $this->venueRepositoryInterface->store($request, $data);
    }
}
