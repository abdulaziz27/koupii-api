<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ValidationHelper;
use App\Helpers\FileUploadHelper;
use DB;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile/{id}",
     *     tags={"Profile"},
     *     summary="Get user details by ID",
     *     description="Retrieve public details of a user by their ID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth (Sanctum)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Referring URL Frontend for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Fika"),
     *             @OA\Property(property="email", type="string", example="student2@example.com"),
     *             @OA\Property(property="avatar", type="string", nullable=true, example="/storage/avatar/6887cfd4a9ec8.png"),
     *             @OA\Property(property="bio", type="string", example="Student from Informatics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'bio' => $user->bio
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/update",
     *     tags={"Profile"},
     *     summary="Update user profile",
     *     description="Update authenticated user's profile including name, email, role, avatar, and bio.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth (Sanctum)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Referring URL Frontend for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
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
     *                 required={"name", "email", "role"},
     *                 @OA\Property(property="name", type="string", example="Fika"),
     *                 @OA\Property(property="email", type="string", format="email", example="student2@example.com"),
     *                 @OA\Property(property="role", type="string", enum={"student", "teacher", "admin"}, example="student"),
     *                 @OA\Property(property="bio", type="string", example="Lorem ipsum dolor sit amet"),
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="User avatar image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="d2fb93ec-6043-4384-814d-0e48f36aed50"),
     *                 @OA\Property(property="name", type="string", example="Fika"),
     *                 @OA\Property(property="email", type="string", example="student2@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="role", type="string", example="student"),
     *                 @OA\Property(property="avatar", type="string", example="/storage/avatar/687f8636256b0.png"),
     *                 @OA\Property(property="bio", type="string", example="Lorem ipsum dolor sit amet"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-17T07:08:54.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-22T12:38:14.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $validator = ValidationHelper::profile($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    FileUploadHelper::delete($user->avatar);
                }
                $data['avatar'] = FileUploadHelper::upload($request->file('avatar'), 'avatar');
            }

            $user->update($data);

            DB::commit();
            return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/profile/destroy",
     *     tags={"Profile"},
     *     summary="Delete user profile",
     *     description="Delete the authenticated user's account, including avatar file if present.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-XSRF-TOKEN",
     *         in="header",
     *         required=false,
     *         description="CSRF token for session-based auth (Sanctum)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Referer",
     *         in="header",
     *         required=false,
     *         description="Referring URL Frontend for CSRF protection",
     *         @OA\Schema(type="string", example="http://localhost:3000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if ($user->avatar) {
                FileUploadHelper::delete($user->avatar);
            }

            $user->delete();
            DB::commit();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }
}
