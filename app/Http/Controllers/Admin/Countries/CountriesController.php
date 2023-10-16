<?php

namespace App\Http\Controllers\Admin\Countries;

use App\Http\Controllers\Controller;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Services\Validations\Country\CountryValidationInterface;
use Illuminate\Http\Request;

class CountriesController extends Controller
{

    function __construct(
        private CountryRepositoryInterface $countryRepositoryInterface,
        private CountryValidationInterface $countryValidationInterface,
    ) {
    }

    function index()
    {
        return $this->countryRepositoryInterface->index();
    }

    function whereHasClubTeams()
    {
        return $this->countryRepositoryInterface->whereHasClubTeams();
    }

    function whereHasNationalTeams()
    {
        return $this->countryRepositoryInterface->whereHasNationalTeams();
    }

    public function store(Request $request)
    {

        $data = $this->countryValidationInterface->store($request);

        return $this->countryRepositoryInterface->store($request, $data);
    }
}
