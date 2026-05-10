<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('instructor_id', Auth::id())->latest()->get();

        return view('instructor.courses.index', compact('courses'));
    }
    
    public function create()
    {
        return view('instructor.courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:100',
            'duration_hours' => 'nullable|integer',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $data['instructor_id'] = Auth::id();

        Course::create($data);

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course created successfully');
    }
}
