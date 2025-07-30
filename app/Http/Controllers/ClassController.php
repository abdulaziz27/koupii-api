<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\ClassEnrollment;
use App\Helpers\ValidationHelper;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Str;
use DB;

class ClassController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/classes",
     *     tags={"Classes"},
     *     summary="Get all classes",
     *     description="Admin & Student can see all classes, Teacher can only see their own classes.",
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
     *         description="List of classes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                 @OA\Property(property="teacher_id", type="string", format="uuid", example="598d528e-c734-456e-b77c-7abee4cf92fa"),
     *                 @OA\Property(property="name", type="string", example="Theorytical Class"),
     *                 @OA\Property(property="description", type="string", example="This is theory class"),
     *                 @OA\Property(property="class_code", type="string", example="lkoiun"),
     *                 @OA\Property(property="cover_image", type="string", example="cover.jpg"),
     *                 @OA\Property(property="is_active", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T07:10:46.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T07:10:46.000000Z"),
     *                 @OA\Property(
     *                     property="teacher",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="598d528e-c734-456e-b77c-7abee4cf92fa"),
     *                     @OA\Property(property="name", type="string", example="Teacher User 1"),
     *                     @OA\Property(property="email", type="string", example="teacher1@example.com"),
     *                     @OA\Property(property="role", type="string", example="teacher"),
     *                     @OA\Property(property="avatar", type="string", nullable=true, example=null),
     *                     @OA\Property(property="bio", type="string", example="Teacher account 1")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $classes = match ($user->role) {
            'admin', => Classes::with(['teacher' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar', 'bio');
            }])->get(),

            'teacher' => Classes::where('teacher_id', $user->id)
                ->with([
                    'teacher' => fn($q) => $q->select('id', 'name', 'email', 'avatar', 'bio')
                ])
                ->get(),

            'student', => Classes::with(['teacher' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar', 'bio');
            }])->select('id', 'teacher_id', 'name', 'description', 'cover_image', 'is_active', 'created_at', 'updated_at')->get(),

            default => null,
        };

        if (is_null($classes)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($classes, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/classes/create",
     *     tags={"Classes"},
     *     summary="Create a new class",
     *     description="Only admin and teacher can create a new class.",
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Theory Class"),
     *                 @OA\Property(property="description", type="string", example="This is theory class"),
     *                 @OA\Property(property="class_code", type="string", example="ABC12345"),
     *                 @OA\Property(property="cover_image", type="string", format="binary", description="Upload class cover image (jpg, png)"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Class created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Class created successfully"),
     *             @OA\Property(property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="7c8461cf-45d7-4ce3-b2c3-dfd3760783be"),
     *                 @OA\Property(property="teacher_id", type="string", format="uuid", example="598d528e-c734-456e-b77c-7abee4cf92fa"),
     *                 @OA\Property(property="name", type="string", example="Arabic Class"),
     *                 @OA\Property(property="description", type="string", example="This is arabic class"),
     *                 @OA\Property(property="class_code", type="string", example="123456"),
     *                 @OA\Property(property="cover_image", type="string", nullable=true, example="cover.jpg"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T08:44:38.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T08:44:38.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=409, description="Class name already exists"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = ValidationHelper::class($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $existingClass = Classes::where('name', $request->input('name'))
                ->where('teacher_id', auth()->user()['id'])
                ->first();

            if ($existingClass) {
                return response()->json(['message' => 'Class name already exists'], 409);
            }

            $data = $validator->validated();

            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = FileUploadHelper::upload($request->file('cover_image'), 'cover');
            }

            $class = Classes::create([
                'teacher_id' => auth()->user()->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'class_code' => $data['class_code'] ?? strtoupper(Str::random(8)),
                'cover_image' => isset($data['cover_image']) ? $data['cover_image'] : null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return response()->json(['message' => 'Class created successfully', 'data' => $class], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/classes/{id}",
     *     tags={"Classes"},
     *     summary="Get class details",
     *     description="Get class detail including teacher info.",
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
     *         description="Class ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Class details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid", example="3d565341-2760-4454-bb36-a89cb2ead1a9"),
     *             @OA\Property(property="teacher_id", type="string", format="uuid", example="571bd78d-4879-44e0-9697-05b6e8bebc5d"),
     *             @OA\Property(property="name", type="string", example="Biology Class"),
     *             @OA\Property(property="description", type="string", example="This is biology class"),
     *             @OA\Property(property="class_code", type="string", example="zxdfrt"),
     *             @OA\Property(property="cover_image", type="string", example="cover.jpg"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T07:32:50.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T07:32:50.000000Z"),
     *             @OA\Property(
     *                 property="teacher",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="571bd78d-4879-44e0-9697-05b6e8bebc5d"),
     *                 @OA\Property(property="name", type="string", example="Teacher User 2"),
     *                 @OA\Property(property="email", type="string", example="teacher2@example.com"),
     *                 @OA\Property(property="avatar", type="string", nullable=true, example=null),
     *                 @OA\Property(property="bio", type="string", example="Teacher account 2")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Class not found")
     * )
     */
    public function show($id)
    {
        $user = auth()->user();

        $classes = match ($user->role) {
            'admin', => Classes::with(['teacher' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar', 'bio');
            }])->get(),

            'teacher' => Classes::where('teacher_id', $user->id)
                ->with([
                    'teacher' => fn($q) => $q->select('id', 'name', 'email', 'avatar', 'bio')
                ])
                ->get(),

            'student', => Classes::with(['teacher' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar', 'bio');
            }])->select('id', 'teacher_id', 'name', 'description', 'cover_image', 'is_active', 'created_at', 'updated_at')->get(),


            default => null,
        };

        $class = $classes->where('id', $id)->first();

        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }
        return response()->json($class, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/classes/update/{id}",
     *     tags={"Classes"},
     *     summary="Update class",
     *     description="Only admin and teacher (class owner) can update class data.",
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
     *         description="Class ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="Override HTTP method for PATCH requests",
     *         @OA\Schema(type="string", example="PATCH")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Biology Class"),
     *                 @OA\Property(property="description", type="string", example="This is biology class"),
     *                 @OA\Property(property="class_code", type="string", example="qwertyui"),
     *                 @OA\Property(property="cover_image", type="string", format="binary", description="Upload class cover image (jpg, png)"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Class updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Class updated successfully"),
     *             @OA\Property(property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="20d7c432-467b-433a-b035-b43481b5ee85"),
     *                 @OA\Property(property="teacher_id", type="string", example="598d528e-c734-456e-b77c-7abee4cf92fa"),
     *                 @OA\Property(property="name", type="string", example="Biology Class"),
     *                 @OA\Property(property="description", type="string", example="This is biology class"),
     *                 @OA\Property(property="class_code", type="string", example="qwertyui"),
     *                 @OA\Property(property="cover_image", type="string", example="cover.jpg"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T07:10:46.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T08:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $class = Classes::find($id);
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $user = auth()->user();
        if ($user->role !== 'admin' && $class->teacher_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $validator = ValidationHelper::class($request->all(), true);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('cover_image')) {
                if ($class->cover_image) {
                    FileUploadHelper::delete($class->cover_image);
                }

                $data['cover_image'] = FileUploadHelper::upload($request->file('cover_image'), 'cover');
            }

            $class->update($data);

            DB::commit();
            return response()->json(['message' => 'Class updated successfully', 'data' => $class], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/classes/delete/{id}",
     *     tags={"Classes"},
     *     summary="Delete a class",
     *     description="Only admin and teacher (class owner) can delete a class.",
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
     *         description="Class ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Class deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Class not found")
     * )
     */
    public function destroy($id)
    {
        $class = Classes::find($id);
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $user = auth()->user();
        if ($user->role !== 'admin' && $class->teacher_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($class->cover_image) {
            FileUploadHelper::delete($class->cover_image);
        }

        $class->delete();
        return response()->json(['message' => 'Class deleted successfully'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/classes/{id}/students",
     *     tags={"Classes"},
     *     summary="Get class students and teacher info",
     *     description="Retrieve class details along with enrolled students and teacher information. Only accessible if the student is enrolled or if user is not a student.",
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
     *         description="Class ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Class and student list returned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="class", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="01983cf4-231b-7001-859d-e49709f12a8d"),
     *                 @OA\Property(property="name", type="string", example="Writing"),
     *                 @OA\Property(property="description", type="string", example="This is class writing"),
     *                 @OA\Property(property="teacher", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="422630f5-d17a-4b88-bacd-1063e69f0e8f"),
     *                     @OA\Property(property="name", type="string", example="Teacher User 1"),
     *                     @OA\Property(property="email", type="string", example="teacher1@example.com"),
     *                     @OA\Property(property="avatar", type="string", nullable=true, example="/storage/avatar/6887cfd4a9ec8.png")
     *                 )
     *             ),
     *             @OA\Property(property="students", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="7429fefd-057c-4714-84e2-3c643c844089"),
     *                     @OA\Property(property="name", type="string", example="Student User 2"),
     *                     @OA\Property(property="email", type="string", example="student2@example.com"),
     *                     @OA\Property(property="avatar", type="string", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - student not enrolled",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Class not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Class not found")
     *         )
     *     )
     * )
     */
    public function students($id)
    {
        $user = auth()->user();

        if ($user->role === 'student') {
            $isEnrolled = ClassEnrollment::where([
                ['class_id', $id],
                ['student_id', $user->id]
            ])->exists();

            if (!$isEnrolled) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $class = Classes::with([
            'teacher:id,name,email,avatar',
            'enrollments.student:id,name,email,avatar'
        ])->find($id);
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        return response()->json([
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'description' => $class->description,
                'teacher' => $class->teacher,
            ],
            'students' => $class->enrollments->pluck('student')->filter()->values()
        ]);
    }
}
