<?php

namespace App\Http\Controllers\Admin\Countries\View;

use App\Http\Controllers\Admin\Countries\CountriesController;
use App\Http\Controllers\Controller;
use App\Repositories\Country\CountryRepositoryInterface;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    function __construct(
        private CountryRepositoryInterface $countryRepositoryInterface,
    ) {
    }

    public function show($id)
    {
        return $this->countryRepositoryInterface->show($id);
    }

    function listCompetitions($id)
    {
        return $this->countryRepositoryInterface->listCompetitions($id);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(CountriesController::class)->store($request);
    }

    function updateStatus($id)
    {
        return $this->countryRepositoryInterface->updateStatus($id);
    }

    function destroy($id)
    {
        return $this->countryRepositoryInterface->destroy($id);
    }
}
