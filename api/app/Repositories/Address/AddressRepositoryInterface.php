<?php

namespace  App\Repositories\Address;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface AddressRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);
}
