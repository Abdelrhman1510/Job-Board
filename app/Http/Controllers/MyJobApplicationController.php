<?php
namespace App\Http\Controllers;


use App\Models\JobApplication;



class MyJobApplicationController extends Controller
{
    public function index()
    {

        return view(
            'my_job_application.index',
            data: [
                'applications' => auth()->user()->jobApplications()
                ->with([
                    'job' => fn($query) => $query->withCount('jobApplications')
                        ->withAvg('jobApplications', 'expected_salary'),
                    'job.employer'
                ])                     ->latest()
                        ->get()
            ]
        );
    }

    public function destroy(JobApplication $jobApplication)
    {
            $jobApplication->delete();
        return redirect()->back()->with('success', 'Job application removed.');
    }


}
