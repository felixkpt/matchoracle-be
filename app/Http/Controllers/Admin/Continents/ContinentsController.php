<?php

namespace App\Http\Controllers\Admin\Continents;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Continent\ContinentRepositoryInterface;
use App\Services\Validations\Continent\ContinentValidationInterface;
use Illuminate\Http\Request;

class ContinentsController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private ContinentRepositoryInterface $continentRepositoryInterface,
        private ContinentValidationInterface $continentValidationInterface,
    ) {
        $this->repo = $continentRepositoryInterface;
    }

    public function index()
    {
        return $this->continentRepositoryInterface->index();
    }

    public function store(Request $request)
    {

        $data = $this->continentValidationInterface->store($request);

        return $this->continentRepositoryInterface->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->continentRepositoryInterface->show($id);
    }

}
