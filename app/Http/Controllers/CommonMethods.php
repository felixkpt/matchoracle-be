<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait CommonMethods
{
    /**
     * The current subject repository.
     */
    protected $repo;

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
