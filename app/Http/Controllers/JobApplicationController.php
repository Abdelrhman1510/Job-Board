<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Log;

class JobApplicationController extends Controller
{

    public function create(Job $job)
    {
        $this->authorize('apply', $job);
        return view('job_application.create', ['job' => $job]);
    }
    public function store(Job $job , Request $request)
    {
        $this->authorize('apply', $job);
        $validatedData = $request->validate([
            'expected_salary' => 'required|min:1|max:1000000',
            'cv' => 'required|file|mimes:pdf|max:2048'
        ]);
        $file = $request->file('cv');
        $path = $file->store('cvs', 'private');
        $job->jobApplications()->create([
            'user_id' => $request->user()->id,
            'expected_salary' => $validatedData['expected_salary'],
            'cv_path' => $path

        ]);
        return redirect()->route('jobs.show', $job)
        ->with('success', 'Job application submitted.');
        }
    public function destroy(string $id)
    {
        //
    }
    public function downloadCV(Job $myJob, User $user)
    {
        // Find the job application by ID
        $jobApplication = JobApplication::where('job_id', $myJob->id)->where('user_id', $user->id)->firstOrFail();
        // dd($jobApplication);

        //Check if the job application has CV
        if (!$jobApplication->cv_path) {
            return redirect()->route('my-jobs.index')
                ->with('error', 'This job application does not have a CV.');
        }
        //Check if the employer of the job application is the same as the logged in user
        if ($myJob->employer->id !== auth()->user()->employer->id) {
            return redirect()->route('my-jobs.index')
                ->with('error', 'You do not have permission to download this CV.');
        }

        // Check if the file exists on the private disk
        if (!Storage::disk('private')->exists($jobApplication->cv_path)) {
            return redirect()->route('my-jobs.index')
                ->with('error', 'This CV file does not exist.');
        }

        // Return the file as a download response
        return Storage::disk('private')->download($jobApplication->cv_path);

    }

}
