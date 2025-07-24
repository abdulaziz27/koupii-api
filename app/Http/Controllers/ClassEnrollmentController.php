<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassEnrollment;
use App\Helpers\ValidationHelper;
use App\Models\Classes;
use DB;

class ClassEnrollmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/enrollments",
     *     tags={"Enrollments"},
     *     summary="Get all enrollments based on user role",
     *     description="Admin sees all enrollments, teacher sees only enrollments in their classes, student sees only their enrolled classes.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Frontend URL for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of enrollments",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="d40a0147-493f-4d70-bb4a-d7052e89e921"),
     *                 @OA\Property(property="class_id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                 @OA\Property(property="student_id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="enrolled_at", type="string", example="2025-07-17 08:55:35"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T08:55:35.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T08:55:35.000000Z"),
     *                 @OA\Property(
     *                     property="class",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                     @OA\Property(property="teacher_id", type="string", example="598d528e-c734-456e-b77c-7abee4cf92fa"),
     *                     @OA\Property(property="name", type="string", example="Theorytical Class"),
     *                     @OA\Property(property="description", type="string", example="This is theory class"),
     *                     @OA\Property(property="class_code", type="string", example="lkoiun"),
     *                     @OA\Property(property="cover_image", type="string", example="cover.jpg"),
     *                     @OA\Property(property="is_active", type="boolean", example=false)
     *                 ),
     *                 @OA\Property(
     *                     property="student",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                     @OA\Property(property="name", type="string", example="Student User 2"),
     *                     @OA\Property(property="email", type="string", example="student2@example.com"),
     *                     @OA\Property(property="role", type="string", example="student")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();

        $enrollments = match ($user->role) {
            'admin' => ClassEnrollment::with(['class', 'student'])->get(),
            'teacher' => ClassEnrollment::with(['class', 'student'])
                ->whereIn('class_id', Classes::where('teacher_id', $user->id)->pluck('id'))
                ->get(),
            'student' => ClassEnrollment::with(['class', 'student'])
                ->where('student_id', $user->id)
                ->get(),
            default => null,
        };

        if (is_null($enrollments)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($enrollments, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/enrollments/create",
     *     tags={"Enrollments"},
     *     summary="Enroll student to class",
     *     description="Student enrolls to a class using class_id and class_code.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Frontend URL for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"class_id","class_code","status"},
     *             @OA\Property(property="class_id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *             @OA\Property(property="class_code", type="string", example="lkoiun"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enrolled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrolled successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="d40a0147-493f-4d70-bb4a-d7052e89e921"),
     *                 @OA\Property(property="class_id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Invalid class code or validation error")
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = ValidationHelper::classEnrollment($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $class = Classes::findOrFail($data['class_id']);

            if ($request->input('class_code') !== $class->class_code) {
                return response()->json(['message' => 'Invalid class code'], 422);
            }

            $enrollment = ClassEnrollment::create([
                'class_id' => $data['class_id'],
                'student_id' => auth()->user()->id,
                'status' => $data['status'],
                'enrolled_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Enrolled successfully', 'data' => $enrollment], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Get enrollment details",
     *     description="Show enrollment details including class and student info.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Frontend URL for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Enrollment ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="d40a0147-493f-4d70-bb4a-d7052e89e921"),
     *             @OA\Property(property="class_id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *             @OA\Property(property="student_id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *             @OA\Property(property="status", type="string", example="active"),
     *             @OA\Property(property="enrolled_at", type="string", example="2025-07-17 08:55:35"),
     *             @OA\Property(property="class", type="object",
     *                 @OA\Property(property="id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                 @OA\Property(property="name", type="string", example="Theorytical Class")
     *             ),
     *             @OA\Property(property="student", type="object",
     *                 @OA\Property(property="id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                 @OA\Property(property="name", type="string", example="Student User 2")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Enrollment not found")
     * )
     */
    public function show($id)
    {
        $enrollment = ClassEnrollment::with(['class', 'student'])->find($id);
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
        return response()->json($enrollment, 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/enrollments/update/{id}",
     *     tags={"Enrollments"},
     *     summary="Update enrollment status",
     *     description="Admin, teacher, or student can update enrollment (depends on role).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Frontend URL for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="inactive"),
     *             @OA\Property(property="_method", type="string", example="PATCH")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="d40a0147-493f-4d70-bb4a-d7052e89e921"),
     *                 @OA\Property(property="status", type="string", example="inactive"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T09:00:46.000000Z")
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $enrollment = ClassEnrollment::find($id);
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        DB::beginTransaction();
        try {
            $validator = ValidationHelper::classEnrollment($request->all(), true);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = auth()->user();

            $unauthorized = [
                'teacher' => $enrollment->class->teacher_id !== $user->id,
                'student' => $enrollment->student_id !== $user->id,
            ];

            if (isset($unauthorized[$user->role]) && $unauthorized[$user->role]) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = $validator->validated();
            $enrollment->update($data);

            DB::commit();
            return response()->json(['message' => 'Enrollment updated successfully', 'data' => $enrollment], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/enrollments/delete/{id}",
     *     tags={"Enrollments"},
     *     summary="Delete enrollment",
     *     description="Admin, teacher, or student can delete enrollment based on role authorization.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Frontend URL for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy($id)
    {
        $enrollment = ClassEnrollment::find($id);
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        $user = auth()->user();

        $unauthorized = [
            'teacher' => $enrollment->class->teacher_id !== $user->id,
            'student' => $enrollment->student_id !== $user->id,
        ];

        if (isset($unauthorized[$user->role]) && $unauthorized[$user->role]) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $enrollment->delete();
        return response()->json(['message' => 'Enrollment deleted successfully'], 200);
    }
}
