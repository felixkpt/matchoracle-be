<?php

namespace App\Http\Controllers\Dashboard\Settings\Picklists\BettingStrategies;

use App\Http\Controllers\Controller;
use App\Repositories\BettingStrategy\BettingStrategyRepositoryInterface;
use App\Services\Validations\BettingStrategy\BettingStrategyValidationInterface;
use Illuminate\Http\Request;

class BettingStrategiesContoller extends Controller
{
    function __construct(
        private BettingStrategyRepositoryInterface $repo,
        private BettingStrategyValidationInterface $validation,
    ) {
    }

    public function index()
    {
        return $this->repo->index();
    }

    public function store(Request $request)
    {

        $data = $this->validation->store($request);

        return $this->repo->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->repo->show($id);
    }

    function updateStatus($id)
    {
        return $this->repo->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->repo->destroy($id);
    }
}
