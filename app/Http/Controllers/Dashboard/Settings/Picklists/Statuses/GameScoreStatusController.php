<?php

namespace App\Http\Controllers\Dashboard\Settings\Picklists\Statuses;

use App\Http\Controllers\Controller;
use App\Repositories\GameScoreStatus\GameScoreStatusRepositoryInterface;
use App\Services\Validations\GameScoreStatus\GameScoreStatusValidationInterface;
use Illuminate\Http\Request;

class GameScoreStatusController extends Controller
{
    function __construct(
        private GameScoreStatusRepositoryInterface $postStatusRepositoryInterface,
        private GameScoreStatusValidationInterface $postStatusValidationInterface,
    ) {
    }

    public function index()
    {
        return $this->postStatusRepositoryInterface->index();
    }

    public function store(Request $request)
    {
        $data = $this->postStatusValidationInterface->store($request);

        return $this->postStatusRepositoryInterface->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->postStatusRepositoryInterface->show($id);
    }

    function updateStatus($id)
    {
        return $this->postStatusRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->postStatusRepositoryInterface->destroy($id);
    }
}
