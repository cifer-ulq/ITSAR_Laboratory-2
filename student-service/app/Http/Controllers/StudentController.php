<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /api/students - List all students
    public function index()
    {
        $students = Student::all();
        return response()->json($students, 200);
    }

    // GET /api/students/{id} - Get one student
    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'error' => 'STUDENT_NOT_FOUND',
                'message' => 'Student with ID ' . $id . ' was not found.'
            ], 404);
        }

        return response()->json($student, 200);
    }

    // POST /api/students - Create a student
    public function store(Request $request)
    {
        // Check if request body is valid JSON
        $data = $request->all();

        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Both name and email fields are required.'
            ], 400);
        }

        // Check for duplicate email
        $existing = Student::where('email', $data['email'])->first();
        if ($existing) {
            return response()->json([
                'error' => 'DUPLICATE_EMAIL',
                'message' => 'A student with this email already exists.'
            ], 409);
        }

        $student = Student::create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return response()->json([
            'id' => $student->id,
            'message' => 'created'
        ], 201);
    }
}
