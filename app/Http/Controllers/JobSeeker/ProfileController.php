<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\Religion;
use App\Models\JobSeeker;
use App\Models\Skill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Configuration;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Education;
use App\Models\EducationLevel;
use App\Models\User;
use App\Models\WorkExperience;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $religions = Religion::all();
        $configurations = Configuration::first();

        $jobseeker = JobSeeker::with('user')->where('id', $user->id)->first();
        $skills = Skill::with('jobseeker')->where('job_seeker_id', $user->id)->get();
        $data = ([
            "title" => "Profile User",
            "jobseeker" => $jobseeker,
            "religions" => $religions,
            "skills" => $skills,
            "configurations" => $configurations
        ]);
        if (!$jobseeker) {
            return view('job-seekers.form-profile', $data);
        }
        return view('job-seekers.profile', $data);
    }

    public function create()
    {
        return view("job-seekers.skills-form");
    }

    public function storeskill(Request $request)
    {
        $data = $request->validate([
            "skill" => "required"
        ]);

        try {
            $data['job_seeker_id'] = auth()->user()->id;
            Skill::create($data);
            Alert::success("Berhasil", "Data Berhasil Ditambahkan");
            return redirect("/profile");
        } catch (\Throwable $th) {
            Alert::error("Gagal", $th->getMessage());
            return back();
        }
    }

    public function editskill(string $id)
    {
        $skill = Skill::with(['jobseeker'])->findOrFail($id);
        $data = ([
            "skill" => $skill,
        ]);

        return view('job-seekers.skills-form', $data);
    }

    public function updateskill(Request $request, string $id)
    {

        $data = $request->validate([
            "skill" => "required"
        ]);

        try {
            $skill = Skill::findOrFail($id);
            $skill->update($data);
            Alert::success("Berhasil", "Data BErhasil Ditambahkan");
            return redirect("/profile");
        } catch (\Throwable $th) {
            Alert::error("Gagal", $th->getMessage());
            return back();
        }
    }

    public function deleteskill(string $id)
    {
        try {
            $skill = Skill::findOrFail($id);
            $skill->delete();
            Alert::success("Sukses", "Hapus Data Sukses");
            return redirect("/profile");
        } catch (\Throwable $th) {
            Alert::error("Gagal", $th->getMessage());
            return back();
        }

    }
    public function store(Request $request)
    {
        $data = $request->validate([
            "photo" => "required|mimes:jpg,png,jpeg|max:512",
            "nik" => "required",
            "religion_id" => "required",
            "birth_date" => "required",
            "first_name" => "required",
            "last_name" => "required",
            "gender" => "required",
            "address" => "required",
            "phone" => "required",
            "description" => "required"
        ]);

        try {
            $data['id'] = auth()->user()->id;
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('img/Job_Seekers_Profile', 'public');
            } else {
                $data['photo'] = null;
            }
            JobSeeker::create($data);
            Alert::success("Suksess", "Data Berhasil Di Input");
            return redirect("/profile");
        } catch (\Throwable $th) {
            Alert::error("Gagal", $th->getMessage());
            return back();
        }
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $jobseeker = JobSeeker::where('id', $user->id)->firstOrFail();

        $religions = Religion::all();

        $data = ([
            "title" => "Edit Data",
            "jobseeker" => $jobseeker,
            "religion" => $religions
        ]);

        return view("job-seekers.form-profile", $data);
    }


    public function edit(string $id)
    {
        $user = Auth::user();
        $jobseeker = JobSeeker::where('id', $user->id)->firstOrFail();
        $religions = Religion::all();

        $data = ([
            "title" => "Edit Data",
            "jobseeker" => $jobseeker,
            "religions" => $religions
        ]);

        return view("job-seekers.form-profile", $data);
    }
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            "photo" => "mimes:jpg,png,jpeg|max:512",
            "nik" => "required",
            "religion_id" => "required",
            "birth_date" => "required",
            "first_name" => "required",
            "last_name" => "required",
            "gender" => "required",
            "address" => "required",
            "phone" => "required",
            "description" => "required"
        ]);

        try {
            $user = Auth::user();
            $jobseeker = JobSeeker::where('id', $user->id)->firstOrFail();
            $data['id'] = auth()->user()->id;

            if ($request->hasFile('photo')) {
                if ($jobseeker->photo) {
                    Storage::delete($jobseeker->photo);
                }

                $data['photo'] = $request->file('photo')->store('img/Job_Seekers_Profile', 'public');
            } else {
                $data['photo'] = $jobseeker->photo;
            }
            $jobseeker->update($data);
            Alert::success('Sukses', 'Edit Data success.');
            return redirect("/profile");
        } catch (\Throwable $th) {
            Alert::error('Gagal', $th->getMessage());
            return back();
        }
    }


    public function showTestimonials()
    {
        $user = Auth::user();

        // Mengambil data job seeker yang sedang login
        $jobseeker = JobSeeker::with('user')->where('id', $user->id)->first();

        // Mengambil semua testimoni
        $testimonis = Testimonial::all();

        // Mengirim data ke view
        return view('job-seekers.index', [
            'jobseeker' => $jobseeker,
            'testimonis' => $testimonis,
        ]);
    }
    public function generateCV()
    {
        $user = Auth::user();
        $jobseeker = JobSeeker::with('user')->where('id', $user->id)->first();
        $skills = Skill::with('jobseeker')->where('job_seeker_id', $user->id)->get();
        $workexperiences = WorkExperience::where('job_seeker_id', $user->id)->get();
        $educations = Education::with('educationlevel')->where('job_seeker_id', $user->id)->get();

        $data = ([
            "jobseeker" => $jobseeker,
            "skills" => $skills,
            "educations" => $educations,
            "workexperiences" => $workexperiences,
            "user" => $user
            
        ]);
        $customPaper = array(0,0,720,1440);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadview('job-seekers.generate-cv', $data)->setPaper($customPaper,'portrait');
        	
        return $pdf->download('curriculum-vitae-'. $jobseeker->first_name . ' ' . $jobseeker->last_name .'.pdf');
    }

}