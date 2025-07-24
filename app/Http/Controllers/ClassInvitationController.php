<?php

namespace App\Http\Controllers;

use App\Models\ClassInvitation;
use App\Models\Classes;
use App\Models\ClassEnrollment;
use App\Helpers\ValidationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassInvitationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/invitations",
     *     tags={"Invitations"},
     *     summary="Get all invitations based on user role",
     *     description="Admin sees all invitations, teacher sees only invitations in their classes, student sees only invitations sent to them.",
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
     *         description="List of invitations",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="caccde25-0463-4acf-979e-46fca9b2315a"),
     *                 @OA\Property(property="class_id", type="string", example="3d565341-2760-4454-bb36-a89cb2ead1a9"),
     *                 @OA\Property(property="teacher_id", type="string", example="571bd78d-4879-44e0-9697-05b6e8bebc5d"),
     *                 @OA\Property(property="student_id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                 @OA\Property(property="email", type="string", example="student2@example.com"),
     *                 @OA\Property(property="invitation_token", type="string", example="ee2df5caeee45080adf83682e6b4c159"),
     *                 @OA\Property(property="status", type="string", example="accepted"),
     *                 @OA\Property(property="expires_at", type="string", example="2025-07-18 09:15:39"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T09:15:39.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T09:18:42.000000Z"),
     *                 @OA\Property(
     *                     property="class",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="3d565341-2760-4454-bb36-a89cb2ead1a9"),
     *                     @OA\Property(property="name", type="string", example="Biology Class"),
     *                     @OA\Property(property="class_code", type="string", example="zxdfrt"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(
     *                     property="student",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                     @OA\Property(property="name", type="string", example="Student User 2"),
     *                     @OA\Property(property="email", type="string", example="student2@example.com")
     *                 ),
     *                 @OA\Property(
     *                     property="teacher",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="571bd78d-4879-44e0-9697-05b6e8bebc5d"),
     *                     @OA\Property(property="name", type="string", example="Teacher User 2")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index()
    {
        $user = auth()->user();

        $invitations = match ($user->role) {
            'admin' => ClassInvitation::with(['class', 'student', 'teacher'])->get(),
            'teacher' => ClassInvitation::with(['class', 'student'])
                ->whereHas('class', fn($q) => $q->where('teacher_id', $user->id))
                ->get(),
            'student' => ClassInvitation::with(['class', 'student', 'teacher' => fn($q) => $q->select('id', 'name')])
                ->where('student_id', $user->id)
                ->get(),
            default => null,
        };

        if (is_null($invitations)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($invitations, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/invitations/create",
     *     tags={"Invitations"},
     *     summary="Send invitation to student",
     *     description="Teacher or admin sends an invitation to a student to join a class.",
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
     *             required={"class_id","student_id","email"},
     *             @OA\Property(property="class_id", type="string", example="3d565341-2760-4454-bb36-a89cb2ead1a9"),
     *             @OA\Property(property="student_id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *             @OA\Property(property="email", type="string", example="student2@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invitation sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invitation sent successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="caccde25-0463-4acf-979e-46fca9b2315a"),
     *                 @OA\Property(property="teacher_id", type="string", example="571bd78d-4879-44e0-9697-05b6e8bebc5d"),
     *                 @OA\Property(property="class_id", type="string", example="3d565341-2760-4454-bb36-a89cb2ead1a9"),
     *                 @OA\Property(property="student_id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                 @OA\Property(property="email", type="string", example="student2@example.com"),
     *                 @OA\Property(property="invitation_token", type="string", example="ee2df5caeee45080adf83682e6b4c159"),
     *                 @OA\Property(property="expires_at", type="string", example="2025-07-18T09:15:39.099827Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=409, description="Student already enrolled or invitation already sent"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = ValidationHelper::classInvitation($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $user = auth()->user();
            $class = Classes::findOrFail($data['class_id']);

            if ($user->role === 'teacher' && $class->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized to invite to this class'], 403);
            }

            $alreadyEnrolled = ClassEnrollment::where('class_id', $data['class_id'])->where('student_id', $data['student_id'])->exists();
            if ($alreadyEnrolled) {
                return response()->json(['message' => 'Student already enrolled in this class'], 409);
            }

            $existingInvitation = ClassInvitation::where('class_id', $data['class_id'])->where('student_id', $data['student_id'])->first();
            if ($existingInvitation) {
                return response()->json(['message' => 'Invitation already sent'], 409);
            }

            $invitation = ClassInvitation::create([
                'teacher_id' => $user->id,
                'class_id' => $data['class_id'],
                'student_id' => $data['student_id'],
                'email' => $data['email'],
                'invitation_token' => bin2hex(random_bytes(16)),
                'expires_at' => now()->addDays(1),
            ]);

            DB::commit();
            return response()->json(['message' => 'Invitation sent successfully', 'data' => $invitation], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/invitations/update/{id}",
     *     tags={"Invitations"},
     *     summary="Update invitation status",
     *     description="Student accepts or declines the invitation.",
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
     *         description="Invitation ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"accepted","declined"}, example="accepted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invitation status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invitation status updated"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="caccde25-0463-4acf-979e-46fca9b2315a"),
     *                 @OA\Property(property="status", type="string", example="accepted"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-17T09:18:42.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Invalid status"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function update(Request $request, $id)
    {
        $invitation = ClassInvitation::find($id);
        if (!$invitation) {
            return response()->json(['message' => 'Invitation not found'], 404);
        }

        $user = auth()->user();

        if ($user->role === 'student' && $invitation->student_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $request->input('status');
        if (!in_array($status, ['accepted', 'declined'])) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        DB::beginTransaction();
        try {
            $invitation->update(['status' => $status]);

            if ($status === 'accepted') {
                ClassEnrollment::firstOrCreate(
                    [
                        'class_id' => $invitation->class_id,
                        'student_id' => $invitation->student_id,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_at' => now(),
                    ],
                );
            }

            DB::commit();
            return response()->json(['message' => 'Invitation status updated', 'data' => $invitation], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/invitations/delete/{id}",
     *     tags={"Invitations"},
     *     summary="Delete invitation",
     *     description="Teacher or admin deletes the invitation before it is accepted or declined.",
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
     *         description="Invitation ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invitation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invitation deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy($id)
    {
        $invitation = ClassInvitation::find($id);
        if (!$invitation) {
            return response()->json(['message' => 'Invitation not found'], 404);
        }

        $user = auth()->user();

        if ($user->role === 'teacher' && $invitation->class->teacher_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $invitation->delete();
        return response()->json(['message' => 'Invitation deleted successfully'], 200);
    }
}
