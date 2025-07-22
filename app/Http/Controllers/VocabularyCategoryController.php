<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VocabularyCategory;
use App\Helpers\ValidationHelper;
use DB;

class VocabularyCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/vocab/categories",
     *     tags={"Vocabulary Categories"},
     *     summary="Get all vocabulary categories",
     *     description="Returns a list of all vocabulary categories.",
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
     *         description="List of vocabulary categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="name", type="string", example="nouns"),
     *                 @OA\Property(property="color_code", type="string", example="#FF0000"),
     *                 @OA\Property(property="created_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $vocabularyCategories = VocabularyCategory::all();
        return response()->json($vocabularyCategories, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/vocab/categories/create",
     *     tags={"Vocabulary Categories"},
     *     summary="Create a new vocabulary category",
     *     description="Adds a new vocabulary category. Requires authentication.",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="nouns"),
     *             @OA\Property(property="color_code", type="string", example="#FF0000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="name", type="string", example="nouns"),
     *                 @OA\Property(property="color_code", type="string", example="#FF0000"),
     *                 @OA\Property(property="created_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $validator = ValidationHelper::vocabularyCategory($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $existingCategory = VocabularyCategory::where('name', $request->input('name'))->first();
            if ($existingCategory) {
                return response()->json(['error' => 'Category name already exists'], 422);
            }

            $category = VocabularyCategory::create($validator->validated());

            DB::commit();
            return response()->json(['message' => 'Category created successfully', 'data' => $category], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error' ,'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/vocab/categories/{id}",
     *     tags={"Vocabulary Categories"},
     *     summary="Get a specific vocabulary category",
     *     description="Retrieve details of a specific category by its ID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the category",
     *         @OA\Schema(type="string", example="uuid-string")
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
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid-string"),
     *             @OA\Property(property="name", type="string", example="nouns"),
     *             @OA\Property(property="color_code", type="string", example="#FF0000"),
     *             @OA\Property(property="created_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show($id)
    {
        $category = VocabularyCategory::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        return response()->json($category, 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/vocab/categories/update/{id}",
     *     tags={"Vocabulary Categories"},
     *     summary="Update an existing vocabulary category",
     *     description="Update a vocabulary category's name or description.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the category",
     *         @OA\Schema(type="string", example="uuid-string")
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="verb"),
     *             @OA\Property(property="color_code", type="string", example="#FFA000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="name", type="string", example="verb"),
     *                 @OA\Property(property="color_code", type="string", example="#FFA000"),
     *                 @OA\Property(property="created_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-07-15T07:19:54.000000Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, $id)
    {
        $category = VocabularyCategory::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        DB::beginTransaction();
        try{
            $validator = ValidationHelper::vocabularyCategory($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $category->update($validator->validated());

            DB::commit();
            return response()->json(['message' => 'Category updated successfully', 'data' => $category], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error' ,'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/vocab/categories/{id}",
     *     tags={"Vocabulary Categories"},
     *     summary="Delete a vocabulary category",
     *     description="Delete a category by its UUID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the category",
     *         @OA\Schema(type="string", example="uuid-string")
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
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy($id)
    {
        $category = VocabularyCategory::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
