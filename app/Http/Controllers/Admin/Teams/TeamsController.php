<?php

namespace App\Http\Controllers\Admin\Teams;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Team;
use App\Repositories\TeamRepository;
use Inertia\Inertia;

class TeamsController extends Controller
{
    private $repo;

    public function __construct(TeamRepository $repo)
    {
        $this->repo = $repo;
    }

    function index()
    {
        $countries = Country::where('has_competitions', true)
        ->whereHas('competitions', function ($q) {
            $q->where('is_domestic', true);
        })
        ->with(['competitions' => function ($q) {
            $q->where('is_domestic', true)
                ->selectRaw('id,country_id,name,img');
        }])
        ->orderBy('priority_no')
        ->get()
        ->toArray();
    
        return response('Teams/Index', compact('countries'));

    }

}
