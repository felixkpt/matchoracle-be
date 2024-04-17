<?php

namespace App\Http\Controllers\Admin\Teams\Addresses;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Address\AddressRepositoryInterface;
use App\Services\Validations\Team\Address\AddressValidationInterface;
use Illuminate\Http\Request;

class AddressesController extends Controller
{

    use CommonControllerMethods;

    function __construct(
        private AddressRepositoryInterface $addressRepositoryInterface,
        private AddressValidationInterface $addressValidationInterface,
    ) {
        $this->repo = $addressRepositoryInterface;
    }

    function index()
    {
        return $this->addressRepositoryInterface->index();
    }

    function store(Request $request)
    {

        if ($request->address_origin == 'source') {

            $data = $this->addressValidationInterface->storeFromSource();

            return $this->addressRepositoryInterface->storeFromSource($request, $data);
        }

        $data = $this->addressValidationInterface->store();

        return $this->addressRepositoryInterface->store($request, $data);
    }
}
