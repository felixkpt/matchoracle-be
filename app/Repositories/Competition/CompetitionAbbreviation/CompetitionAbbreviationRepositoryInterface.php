<?php

namespace App\Repositories\Competition\CompetitionAbbreviation;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CompetitionAbbreviationRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);

}
