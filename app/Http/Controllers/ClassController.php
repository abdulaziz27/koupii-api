<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
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
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of classes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid", example="0199800b-be72-71ed-91bc-118cbfdadc22"),
     *                 @OA\Property(property="name", type="string", example="Biology Class"),
     *                 @OA\Property(property="description", type="string", example="This is class biology"),
     *                 @OA\Property(property="cover_image", type="string", nullable=true, example=null),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T08:44:37.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-29T07:35:08.000000Z"),
     *                 @OA\Property(
     *                     property="teacher",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="240726c6-64b3-4cbb-ae47-923dc2b46d37"),
     *                     @OA\Property(property="name", type="string", example="Fika Teacher"),
     *                     @OA\Property(property="email", type="string", example="teacher1@example.com"),
     *                     @OA\Property(property="bio", type="string", example="Lorem ipsum dolor sit amet"),
     *                     @OA\Property(property="avatar", type="string", nullable=true, example="http://localhost:8000/storage/avatar/68d3bb777d57b.png")
     *                 ),
     *                 @OA\Property(
     *                     property="students",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="e76ed3e1-f9ea-45b4-84a3-275fa0c24715"),
     *                         @OA\Property(property="name", type="string", example="Fita"),
     *                         @OA\Property(property="avatar", type="string", nullable=true, example="http://localhost:8000/storage/avatar/68d50da003002.png")
     *                     )
     *                 ),
     *                 @OA\Property(property="class_code", type="string", example="1234567")
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
            'admin' => Classes::with([
                'teacher:id,name,email,avatar,bio',
                'students:id,name,email,avatar'
            ])->get(),

            'teacher' => Classes::where('teacher_id', $user->id)
                ->with([
                    'teacher:id,name,email,avatar,bio',
                    'students:id,name,email,avatar'
                ])->get(),

            'student' => Classes::whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
                ->with([
                    'teacher:id,name,email,avatar,bio',
                    'students:id,name,email,avatar',
                ])
                ->get(),

            default => null,
        };

        if (is_null($classes)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $classes = $classes->map(function ($class) use ($user) {
            $base = [
                'id'          => $class->id,
                'name'        => $class->name,
                'description' => $class->description,
                'cover_image' => $class->cover_image ? url($class->cover_image) : null,
                'is_active'   => $class->is_active,
                'created_at'  => $class->created_at,
                'updated_at'  => $class->updated_at,
                'teacher'     => $class->teacher ? [
                    'id'     => $class->teacher->id,
                    'name'   => $class->teacher->name,
                    'email'  => $class->teacher->email,
                    'bio'    => $class->teacher->bio,
                    'avatar' => $class->teacher->avatar ? url($class->teacher->avatar) : null,
                ] : null,
                'students' => $class->students->map(function ($student) {
                    return [
                        'id'         => $student->id,
                        'name'       => $student->name,
                        'avatar'     => $student->avatar ? url($student->avatar) : null,
                    ];
                }),
            ];

            if (in_array($user->role, ['admin', 'teacher'])) {
                $base['class_code'] = $class->class_code;
            }

            return $base;
        });

        return response()->json($classes, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/classes/create",
     *     tags={"Classes"},
     *     summary="Create a new class",
     *     description="Only admin and teacher can create a new class.",
     *     security={{"bearerAuth":{}}},
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
     *             @OA\Property(property="message", type="string", example="Class created successfully")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Class name or code already exists"),
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

            $existingClassCode = Classes::where('class_code', $request->input('class_code'))
                ->where('teacher_id', auth()->user()['id']);

            if ($existingClassCode->exists()) {
                return response()->json(['message' => 'Class code already exists'], 409);
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
            return response()->json(['message' => 'Class created successfully'], 201);
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
     *     security={{"bearerAuth":{}}},
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
     *             @OA\Property(property="id", type="string", format="uuid", example="0199800b-be72-71ed-91bc-118cbfdadc22"),
     *             @OA\Property(property="name", type="string", example="Biology Class"),
     *             @OA\Property(property="description", type="string", example="This is class biology"),
     *             @OA\Property(property="cover_image", type="string", nullable=true, example=null),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T08:44:37.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-29T07:35:08.000000Z"),
     *             @OA\Property(
     *                 property="teacher",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="240726c6-64b3-4cbb-ae47-923dc2b46d37"),
     *                 @OA\Property(property="name", type="string", example="Fika Teacher"),
     *                 @OA\Property(property="email", type="string", example="teacher1@example.com"),
     *                 @OA\Property(property="bio", type="string", example="Lorem ipsum dolor sit amet"),
     *                 @OA\Property(property="avatar", type="string", nullable=true, example="http://localhost:8000/storage/avatar/68d3bb777d57b.png")
     *             ),
     *             @OA\Property(
     *                 property="students",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="e76ed3e1-f9ea-45b4-84a3-275fa0c24715"),
     *                     @OA\Property(property="name", type="string", example="Fita"),
     *                     @OA\Property(property="avatar", type="string", nullable=true, example="http://localhost:8000/storage/avatar/68d50da003002.png")
     *                 )
     *             ),
     *             @OA\Property(property="class_code", type="string", example="1234567")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Class not found")
     * )
     */
    public function show($id)
    {
        $user = auth()->user();

        $class = match ($user->role) {
            'admin' => Classes::with([
                'teacher:id,name,email,avatar,bio',
                'students:id,name,email,avatar',
            ])->find($id),

            'teacher' => Classes::where('teacher_id', $user->id)
                ->with([
                    'teacher:id,name,email,avatar,bio',
                    'students:id,name,email,avatar',
                ])
                ->find($id),

            'student' => Classes::whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
                ->with([
                    'teacher:id,name,email,avatar,bio',
                    'students:id,name,email,avatar',
                ])
                ->find($id),

            default => null,
        };

        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $data = [
            'id'          => $class->id,
            'name'        => $class->name,
            'description' => $class->description,
            'cover_image' => $class->cover_image ? url($class->cover_image) : null,
            'is_active'   => $class->is_active,
            'created_at'  => $class->created_at,
            'updated_at'  => $class->updated_at,
            'teacher'     => $class->teacher ? [
                'id'     => $class->teacher->id,
                'name'   => $class->teacher->name,
                'email'  => $class->teacher->email,
                'bio'    => $class->teacher->bio,
                'avatar' => $class->teacher->avatar ? url($class->teacher->avatar) : null,
            ] : null,
            'students' => $class->students->map(function ($student) {
                return [
                    'id'          => $student->id,
                    'name'        => $student->name,
                    'avatar'      => $student->avatar ? url($student->avatar) : null,
                ];
            }),
        ];

        if (in_array($user->role, ['admin', 'teacher'])) {
            $data['class_code'] = $class->class_code;
        }

        return response()->json($data, 200);
    }


    /**
     * @OA\Post(
     *     path="/api/classes/update/{id}",
     *     tags={"Classes"},
     *     summary="Update class",
     *     description="Only admin and teacher (class owner) can update class data.",
     *     security={{"bearerAuth":{}}},
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
     *             @OA\Property(property="message", type="string", example="Class updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Class not found"),
     *     @OA\Response(response=409, description="Class name or code already exists"),
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

            if ($request->filled('name')) {
                $existingClass = Classes::where('name', $request->input('name'))
                    ->where('teacher_id', auth()->id())
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingClass) {
                    return response()->json(['message' => 'Class name already exists'], 409);
                }
            }

            if ($request->filled('class_code')) {
                $existingClassCode = Classes::where('class_code', $request->input('class_code'))
                    ->where('teacher_id', auth()->id())
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingClassCode) {
                    return response()->json(['message' => 'Class code already exists'], 409);
                }
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
            return response()->json(['message' => 'Class updated successfully'], 200);
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
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Class ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Class deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Class deleted successfully")
     *         )
     *     ),
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
}
