<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // GET /api/courses - List all courses
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses, 200);
    }

    // GET /api/courses/{id} - Get one course
    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'error' => 'COURSE_NOT_FOUND',
                'message' => 'Course with ID ' . $id . ' was not found.'
            ], 404);
        }

        return response()->json($course, 200);
    }

    // POST /api/courses - Create a course
    public function store(Request $request)
    {
        $data = $request->all();

        // Validate required fields
        if (empty($data['name']) || empty($data['code'])) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Both name and code fields are required.'
            ], 400);
        }

        // Check for duplicate course code
        $existing = Course::where('code', $data['code'])->first();
        if ($existing) {
            return response()->json([
                'error' => 'DUPLICATE_CODE',
                'message' => 'A course with this code already exists.'
            ], 409);
        }

        $course = Course::create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return response()->json([
            'id' => $course->id,
            'message' => 'created'
        ], 201);
    }
}
