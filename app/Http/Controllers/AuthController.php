<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ValidationHelper;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Registers a user and logs them in",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role"},
     *             @OA\Property(property="name", type="string", example="Fika Riyadi"),
     *             @OA\Property(property="email", type="string", format="email", example="fika@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="role", type="string", example="user"),
     *         )
     *     ),
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
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registered & Logged in successfully")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = ValidationHelper::register($request->all());
            if ($validated->fails()) {
                return response()->json(
                    [
                        'message' => 'Validation failed',
                        'errors' => $validated->errors(),
                    ],
                    422,
                );
            }

            $data = $validated->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            Auth::login($user);
            $request->session()->regenerate();
            DB::commit();

            return response()->json(['message' => 'Registered & Logged in successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     description="Authenticates a user using email and password. Requires X-XSRF-TOKEN header if using Sanctum with session-based auth.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="fika@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
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
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged in successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = ValidationHelper::login($request->all());
            if ($validator->fails()) {
                return response()->json(
                    [
                        'message' => 'Validation error',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $data = $validator->validated();

            if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $request->session()->regenerate();
            DB::commit();
            return response()->json(['message' => 'Logged in successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout the current user",
     *     description="Invalidates the session and logs out the user. Requires X-XSRF-TOKEN header if using session-based Sanctum.",
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
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Logout failed")
     * )
     */
    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Auth"},
     *     summary="Get current authenticated user",
     *     description="Returns the authenticated user's profile data. Requires CSRF token in session-based Sanctum.",
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
     *         description="Authenticated user data",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Fika Riyadi"),
     *             @OA\Property(property="email", type="string", example="fika@example.com"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
     *             @OA\Property(property="bio", type="string", example="Frontend Developer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Get(
     *     path="/sanctum/csrf-cookie",
     *     summary="Get CSRF token cookie for Sanctum",
     *     tags={"Auth"},
     *     description="This endpoint sets the XSRF-TOKEN cookie required for CSRF protection in session-based auth. Usually called before login.",
     *     @OA\Response(
     *         response=204,
     *         description="CSRF cookie set successfully",
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="XSRF-TOKEN and/or session cookie",
     *             @OA\Schema(type="string", example="XSRF-TOKEN=abc123; Path=/; Secure; HttpOnly; SameSite=Lax")
     *         )
     *     )
     * )
     */
    public function sanctum(Request $request)
    {
        return response()->json(['message' => 'CSRF cookie set']);
    }
}
