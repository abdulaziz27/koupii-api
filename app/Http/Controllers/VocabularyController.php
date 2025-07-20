<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vocabulary;
use App\Helpers\ValidationHelper;
use DB;

class VocabularyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/vocab/vocabularies",
     *     tags={"Vocabulary"},
     *     summary="Get all vocabularies",
     *     description="Returns a list of all vocabulary entries.",
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
     *         description="List of vocabulary entries",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="teacher_id", type="string", example="uuid-teacher"),
     *                 @OA\Property(property="category_id", type="string", example="uuid-category"),
     *                 @OA\Property(property="word", type="string", example="Elephant"),
     *                 @OA\Property(property="translation", type="string", example="Gajah"),
     *                 @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *                 @OA\Property(property="explanation", type="string", example="A large mammal with trunk."),
     *                 @OA\Property(property="audio_file_path", type="string", example="audio/elephant.mp3"),
     *                 @OA\Property(property="is_public", type="boolean", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-01T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-01T10:00:00Z"),
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $vocabularies = Vocabulary::all();
        return response()->json($vocabularies, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/vocab/create",
     *     tags={"Vocabulary"},
     *     summary="Create a new vocabulary entry",
     *     description="Adds a new vocabulary entry to the system.",
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"category_id","word","translation","is_public"},
     *                 @OA\Property(property="category_id", type="string", example="uuid-category"),
     *                 @OA\Property(property="word", type="string", example="Elephant"),
     *                 @OA\Property(property="translation", type="string", example="Gajah"),
     *                 @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *                 @OA\Property(property="explanation", type="string", example="A large mammal with trunk."),
     *                 @OA\Property(property="audio_file_path", type="file", example="audio/elephant.mp3"),
     *                 @OA\Property(property="is_public", type="boolean", example=1)
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Vocabulary created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vocabulary created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="teacher_id", type="string", example="uuid-teacher"),
     *                 @OA\Property(property="category_id", type="string", example="uuid-category"),
     *                 @OA\Property(property="word", type="string", example="Elephant"),
     *                 @OA\Property(property="translation", type="string", example="Gajah"),
     *                 @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *                 @OA\Property(property="explanation", type="string", example="A large mammal with trunk."),
     *                 @OA\Property(property="audio_file_path", type="string", example="audio/elephant.mp3"),
     *                 @OA\Property(property="is_public", type="boolean", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-01T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-01T10:00:00Z")
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
            $validator = ValidationHelper::vocabulary($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $existingVocabulary = Vocabulary::where('word', $request->word)->first();
            if ($existingVocabulary) {
                return response()->json(['error' => 'Vocabulary word already exists'], 422);
            }

            $data = $validator->validated();

            $vocabulary = Vocabulary::create([
                'teacher_id' => auth()->user()->id,
                'category_id' => $data['category_id'],
                'word' => $data['word'],
                'translation' => $data['translation'],
                'spelling' => $data['spelling'],
                'explanation' => $data['explanation'],
                'audio_file_path' => isset($data['audio_file_path']),
                'is_public' => $data['is_public'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Vocabulary created successfully', 'data' => $vocabulary], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error' ,'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/vocab/{id}",
     *     tags={"Vocabulary"},
     *     summary="Get a specific vocabulary entry",
     *     description="Retrieve details of a vocabulary entry by its ID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the vocabulary",
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
     *         description="Vocabulary retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid-string"),
     *             @OA\Property(property="teacher_id", type="string", example="uuid-teacher"),
     *             @OA\Property(property="category_id", type="string", example="uuid-category"),
     *             @OA\Property(property="word", type="string", example="Elephant"),
     *             @OA\Property(property="translation", type="string", example="Gajah"),
     *             @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *             @OA\Property(property="explanation", type="string", example="A large mammal with trunk."),
     *             @OA\Property(property="audio_file_path", type="string", example="audio/elephant.mp3"),
     *             @OA\Property(property="is_public", type="boolean", example=1),
     *             @OA\Property(property="created_at", type="string", example="2025-08-01T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", example="2025-08-01T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Vocabulary not found")
     * )
     */
    public function show($id)
    {
        $vocabulary = Vocabulary::find($id);
        if (!$vocabulary) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }
        return response()->json($vocabulary, 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/vocab/update/{id}",
     *     tags={"Vocabulary"},
     *     summary="Update a vocabulary entry",
     *     description="Updates an existing vocabulary entry.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the vocabulary",
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
     *                 @OA\Property(property="category_id", type="string", example="uuid-category"),
     *                 @OA\Property(property="word", type="string", example="Elephant Updated"),
     *                 @OA\Property(property="translation", type="string", example="Gajah Besar"),
     *                 @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *                 @OA\Property(property="explanation", type="string", example="Updated explanation"),
     *                 @OA\Property(property="audio_file_path", type="file", example="audio/elephant-new.mp3"),
     *                 @OA\Property(property="is_public", type="boolean", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vocabulary updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vocabulary updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid-string"),
     *                 @OA\Property(property="teacher_id", type="string", example="uuid-teacher"),
     *                 @OA\Property(property="category_id", type="string", example="uuid-category"),
     *                 @OA\Property(property="word", type="string", example="Elephant Updated"),
     *                 @OA\Property(property="translation", type="string", example="Gajah Besar"),
     *                 @OA\Property(property="spelling", type="string", example="ˈeləfənt"),
     *                 @OA\Property(property="explanation", type="string", example="Updated explanation"),
     *                 @OA\Property(property="audio_file_path", type="string", example="audio/elephant-new.mp3"),
     *                 @OA\Property(property="is_public", type="boolean", example=0),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-01T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-01T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Vocabulary not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, $id)
    {
        $vocabulary = Vocabulary::find($id);
        if (!$vocabulary) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        DB::beginTransaction();
        try{
            $validator = ValidationHelper::vocabulary($request->all());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $existingVocabulary = Vocabulary::where('word', $request->word)->where('id', '!=', $id)->first();
            if ($existingVocabulary) {
                return response()->json(['error' => 'Vocabulary word already exists'], 422);
            }

            $data = $validator->validated();

            $vocabulary->update([
                'teacher_id' => auth()->user()->id,
                'category_id' => $data['category_id'],
                'word' => $data['word'],
                'translation' => $data['translation'],
                'spelling' => $data['spelling'],
                'explanation' => $data['explanation'],
                'audio_file_path' => isset($data['audio_file_path']) ? $data['audio_file_path'] : $vocabulary->audio_file_path,
                'is_public' => $data['is_public'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Vocabulary updated successfully', 'data' => $vocabulary], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server error' ,'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/vocab/delete/{id}",
     *     tags={"Vocabulary"},
     *     summary="Delete a vocabulary entry",
     *     description="Deletes a vocabulary entry by its UUID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the vocabulary",
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
     *         description="Vocabulary deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vocabulary deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Vocabulary not found")
     * )
     */
    public function destroy($id)
    {
        $vocabulary = Vocabulary::find($id);
        if (!$vocabulary) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        $vocabulary->delete();
        return response()->json(['message' => 'Vocabulary deleted successfully'], 200);
    }
}
