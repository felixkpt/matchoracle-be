<?php

namespace App\Http\Controllers\Dashboard\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobsController extends Controller
{
    /**
     * Update the status of a job/process.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'status' => 'required|string',
        ]);

        // Find the job by its ID
        $job = Job::findOrFail($id);

        // Update the job status
        $job->status = $request->input('status');
        $job->save();

        return response()->json([
            'message' => 'Job status updated successfully',
            'job' => $job,
        ], 200);
    }
}
