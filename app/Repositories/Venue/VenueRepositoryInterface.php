<?php

namespace  App\Repositories\Venue;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface VenueRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);
}
