<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ValidationHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use DB;

class PasswordController extends Controller
{
    /**
     * @OA\Patch(
     *     path="/api/password/change-password",
     *     tags={"Password"},
     *     summary="Change user password",
     *     description="Change the authenticated user's password. Requires the current password and a new one. CSRF protection via XSRF-TOKEN and Referer header.",
     *     operationId="changeUserPassword",
     *     security={{"sanctum":{}}},
     *
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
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="Password123!"),
     *             @OA\Property(property="new_password", type="string", format="password", example="Passwordkece123!"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="Passwordkece123!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "current_password": {"Current password is incorrect"},
     *                     "new_password": {"The new password must be at least 8 characters."}
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated or current password incorrect",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $validator = ValidationHelper::changePassword($request->all());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::find(auth()->id());

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 401);
            }

            DB::beginTransaction();

            $user->update([
                'password' => bcrypt($request->input('new_password')),
            ]);

            Auth::guard('web')->login($user);

            DB::commit();
            return response()->json(['message' => 'Password changed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }
}
