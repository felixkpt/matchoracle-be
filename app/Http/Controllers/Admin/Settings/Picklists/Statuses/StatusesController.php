<?php

namespace App\Http\Controllers\Admin\Settings\Picklists\Statuses;

use App\Http\Controllers\Controller;
use App\Repositories\Status\StatusRepositoryInterface;
use App\Services\Validations\Status\StatusValidationInterface;
use Illuminate\Http\Request;

class StatusesController extends Controller
{
    function __construct(
        private StatusRepositoryInterface $statusRepositoryInterface,
        private StatusValidationInterface $statusValidationInterface,
    ) {
    }

    public function index()
    {
        return $this->statusRepositoryInterface->index();
    }

    public function store(Request $request)
    {
        $data = $this->statusValidationInterface->store($request);

        return $this->statusRepositoryInterface->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->statusRepositoryInterface->show($id);
    }

    function updateStatus($id)
    {
        return $this->statusRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->statusRepositoryInterface->destroy($id);
    }
}
