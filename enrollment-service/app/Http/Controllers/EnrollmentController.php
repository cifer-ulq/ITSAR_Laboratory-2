<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EnrollmentController extends Controller
{
    // Base URLs for the other services
    private $studentServiceUrl = 'http://127.0.0.1:8001/api';
    private $courseServiceUrl = 'http://127.0.0.1:8002/api';

    // GET /api/enrollments - List all enrollments
    public function index()
    {
        $enrollments = Enrollment::all();
        return response()->json($enrollments, 200);
    }

    // POST /api/enrollments - Create an enrollment
    public function store(Request $request)
    {
        $data = $request->all();

        // 400 - Validate required fields
        if (empty($data['student_id']) || empty($data['course_id'])) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Both student_id and course_id are required.'
            ], 400);
        }

        // Check if student exists (calls Student Service)
        $studentCheck = $this->checkStudent($data['student_id']);
        if ($studentCheck !== true) {
            return $studentCheck; // Returns error response
        }

        // Check if course exists (calls Course Service)
        $courseCheck = $this->checkCourse($data['course_id']);
        if ($courseCheck !== true) {
            return $courseCheck; // Returns error response
        }

        // 409 - Check for duplicate enrollment
        $existing = Enrollment::where('student_id', $data['student_id'])
            ->where('course_id', $data['course_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'DUPLICATE_ENROLLMENT',
                'message' => 'This student is already enrolled in this course.'
            ], 409);
        }

        // Create the enrollment
        $enrollment = Enrollment::create([
            'student_id' => $data['student_id'],
            'course_id' => $data['course_id'],
        ]);

        return response()->json([
            'id' => $enrollment->id,
            'message' => 'created'
        ], 201);
    }

    // Helper: Check if student exists via Student Service
    private function checkStudent($studentId)
    {
        try {
            $response = Http::timeout(5)
                ->get($this->studentServiceUrl . '/students/' . $studentId);

            if ($response->status() === 404) {
                return response()->json([
                    'error' => 'STUDENT_NOT_FOUND',
                    'message' => 'Student with ID ' . $studentId . ' was not found.'
                ], 404);
            }

            return true;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // 503 - Service is down or 504 - Timeout
            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                return response()->json([
                    'error' => 'GATEWAY_TIMEOUT',
                    'message' => 'Student Service is taking too long to respond.'
                ], 504);
            }

            return response()->json([
                'error' => 'SERVICE_UNAVAILABLE',
                'message' => 'Student Service is currently unavailable.'
            ], 503);
        }
    }

    // Helper: Check if course exists via Course Service
    private function checkCourse($courseId)
    {
        try {
            $response = Http::timeout(5)
                ->get($this->courseServiceUrl . '/courses/' . $courseId);

            if ($response->status() === 404) {
                return response()->json([
                    'error' => 'COURSE_NOT_FOUND',
                    'message' => 'Course with ID ' . $courseId . ' was not found.'
                ], 404);
            }

            return true;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // 503 - Service is down or 504 - Timeout
            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                return response()->json([
                    'error' => 'GATEWAY_TIMEOUT',
                    'message' => 'Course Service is taking too long to respond.'
                ], 504);
            }

            return response()->json([
                'error' => 'SERVICE_UNAVAILABLE',
                'message' => 'Course Service is currently unavailable.'
            ], 503);
        }
    }
}
