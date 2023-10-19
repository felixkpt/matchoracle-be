<?php

namespace App\Http\Controllers\Admin\Settings\Picklists\GameSources;

use App\Http\Controllers\Controller;
use App\Repositories\GameSource\GameSourceRepositoryInterface;
use App\Services\Validations\GameSource\GameSourceValidationInterface;
use Illuminate\Http\Request;

class GameSourcesController extends Controller
{
    function __construct(
        private GameSourceRepositoryInterface $gameSourceRepositoryInterface,
        private GameSourceValidationInterface $gameSourceValidationInterface,
    ) {
    }

    public function index()
    {
        return $this->gameSourceRepositoryInterface->index();
    }

    public function store(Request $request)
    {

        $data = $this->gameSourceValidationInterface->store($request);

        return $this->gameSourceRepositoryInterface->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->gameSourceRepositoryInterface->show($id);
    }

    function updateStatus($id)
    {
        return $this->gameSourceRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->gameSourceRepositoryInterface->destroy($id);
    }
}
