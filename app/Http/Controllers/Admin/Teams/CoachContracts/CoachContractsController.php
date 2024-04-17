<?php

namespace App\Http\Controllers\Admin\Teams\CoachContracts;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\CoachContract\CoachContractRepositoryInterface;
use App\Services\Validations\Team\CoachContract\CoachContractValidationInterface;
use Illuminate\Http\Request;

class CoachContractsController extends Controller
{

    use CommonControllerMethods;

    function __construct(
        private CoachContractRepositoryInterface $contractRepositoryInterface,
        private CoachContractValidationInterface $contractValidationInterface,
    ) {
        $this->repo = $contractRepositoryInterface;
    }

    function index()
    {
        return $this->contractRepositoryInterface->index();
    }

    function store(Request $request)
    {

        $data = $this->contractValidationInterface->store();

        return $this->contractRepositoryInterface->store($request, $data);
    }
}
