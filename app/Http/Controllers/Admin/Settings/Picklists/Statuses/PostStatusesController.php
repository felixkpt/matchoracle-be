<?php

namespace App\Http\Controllers\Admin\Settings\Picklists\Statuses;

use App\Http\Controllers\Controller;
use App\Models\PostStatus;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostStatusesController extends Controller
{
    public function index()
    {

        $statuses = PostStatus::query();

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->sortable(['id', 'name'])
            ->addColumn('action', function ($status) {
                return '
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="icon icon-list2 font-20"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item autotable-view" data-id="' . $status->id . '" href="/admin/settings/picklists/statuses/post/' . $status->id . '">View</a></li>
                <li><a class="dropdown-item autotable-edit" data-id="' . $status->id . '" href="/admin/settings/picklists/statuses/post/' . $status->id . '">Edit</a></li>
                <li><a class="dropdown-item autotable-status-update" data-id="' . $status->id . '" href="/admin/settings/picklists/statuses/post/' . $status->id . '/status-update">' . ($status->status == 1 ? 'Deactivate' : 'Activate') . '</a></li>
            </ul>
        </div>
        ';
            })
            ->statuses(\App\Models\PostStatus::class)
            ->paginate();

        return response(['results' => $statuses]);
    }

    public function store(Request $request)
    {

        $data = $request->all();

        $validateUser = Validator::make(
            $data,
            [
                'name' => 'required|string|unique:post_statuses,name,' . $request->id . ',id',
                'description' => 'required|string',
                'icon' => 'required|string',

            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        $res = PostStatus::updateOrCreate(['id' => $request->id], $data);
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }


    public function show($id)
    {
        $status = PostStatus::findOrFail($id);
        return response()->json([
            'results' => $status,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
