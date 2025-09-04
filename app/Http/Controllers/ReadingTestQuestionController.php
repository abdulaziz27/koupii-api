<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Passage;
use App\Models\QuestionGroup;
use App\Models\TestQuestion;
use App\Models\QuestionOption;
use App\Models\QuestionBreakdown;
use App\Models\HighlightSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ValidationHelper;
use App\Helpers\FileUploadHelper;

class ReadingTestQuestionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Test::query()->where('type', 'reading')
                ->with([
                    'passages.questionGroups.questions.options',
                    'passages.questionGroups.questions.breakdowns.highlightSegments',
                    'creator'
                ]);

            if ($user->role === 'admin') {
                // Admins can access any test
            } elseif ($user->role === 'student') {
                // Students can only access published tests
                $query->where('is_published', true);
            } else {
                // Non-admin, non-student users (e.g., teachers/creators) can only access their own tests
                $query->where('creator_id', $user->id);
            }

            $tests = $query->get()->map(function ($test) use ($user) {
                $isStudent = $user->role === 'student';
                return [
                    'id' => $test->id,
                    'creator_id' => $test->creator_id,
                    'creator_name' => $test->creator ? $test->creator->name : null,
                    'test_type' => $test->test_type,
                    'type' => $test->type,
                    'difficulty' => $test->difficulty,
                    'title' => $test->title,
                    'description' => $test->description,
                    'timer_mode' => $test->timer_mode,
                    'timer_settings' => $test->timer_settings,
                    'allow_repetition' => $test->allow_repetition,
                    'max_repetition_count' => $test->max_repetition_count,
                    'is_public' => $test->is_public,
                    'is_published' => $test->is_published,
                    'settings' => $test->settings,
                    'created_at' => $test->created_at,
                    'updated_at' => $test->updated_at,
                    'passages' => $test->passages->map(function ($passage) use ($isStudent) {
                        return [
                            'passage_id' => $passage->id,
                            'title' => $passage->title,
                            'description' => $passage->description,
                            'question_groups' => $passage->questionGroups->map(function ($group) use ($isStudent) {
                                $questions = $group->questions;

                                // Define the main question: the one without a dot (.)
                                $mainQuestion = $questions->first(function ($q) {
                                    return strpos((string)$q->question_number, '.') === false;
                                });

                                // Fallback if there is no explicit main (e.g., all use 2.1, 2.2)
                                if (!$mainQuestion) {
                                    $mainQuestion = $questions->sortBy('question_number')->first();
                                }

                                if (!$mainQuestion || $mainQuestion->question_type !== 'Matching Heading') {
                                    return [
                                        'instruction' => $group->instruction,
                                        'questions' => $questions->map(function ($question) use ($isStudent) {
                                            $questionData = [
                                                'question_id' => $question->id,
                                                'question_type' => $question->question_type,
                                                'question_number' => $question->question_number,
                                                'question_text' => $question->question_text,
                                                'question_data' => $question->question_data,
                                                'points_value' => $question->points_value,
                                                'options' => $question->options->map(function ($option) {
                                                    return [
                                                        'option_key' => $option->option_key,
                                                        'option_text' => $option->option_text,
                                                    ];
                                                })->toArray(),
                                            ];

                                            if (!$isStudent) {
                                                $correctAnswers = $question->correct_answers;
                                                $decodedAnswers = is_array($correctAnswers) ? $correctAnswers : json_decode($correctAnswers, true) ?? [];
                                                $questionData['correct_answers'] = is_array($decodedAnswers) && count($decodedAnswers) === 1 ? $decodedAnswers[0] : $decodedAnswers;
                                                $questionData['breakdown'] = $question->breakdowns->map(function ($breakdown) {
                                                    $highlights = $breakdown->highlightSegments->map(function ($highlight) {
                                                        return [
                                                            'start_char_index' => $highlight->start_char_index,
                                                            'end_char_index' => $highlight->end_char_index,
                                                        ];
                                                    })->toArray();
                                                    return [
                                                        'explanation' => $breakdown->explanation,
                                                        'has_highlight' => $breakdown->has_highlight,
                                                        'highlights' => is_array($highlights) && count($highlights) === 1 ? $highlights[0] : $highlights,
                                                    ];
                                                })->first();
                                            }

                                            return $questionData;
                                        })->toArray(),
                                    ];
                                }

                                // Group items under the main question
                                $items = $questions->filter(function ($q) use ($mainQuestion) {
                                    return $q->id !== $mainQuestion->id && strpos((string)$q->question_number, '.') !== false;
                                })->map(function ($item) use ($isStudent) {
                                    $itemData = [
                                        'question_id' => $item->id,
                                        'question_number' => $item->question_number,
                                    ];

                                    if (!$isStudent) {
                                        $correctAnswers = json_decode($item->correct_answers, true);
                                        $itemData['correct_answers'] = is_array($correctAnswers) && count($correctAnswers) === 1 ? $correctAnswers[0] : ($correctAnswers ?? []);
                                    }

                                    return $itemData;
                                })->values();

                                $mainQuestionData = [
                                    'question_id' => $mainQuestion->id,
                                    'question_type' => $mainQuestion->question_type,
                                    'question_number' => $mainQuestion->question_number,
                                    'question_data' => $mainQuestion->question_data,
                                    'points_value' => $mainQuestion->points_value,
                                    'options' => $mainQuestion->options->map(function ($option) {
                                        return [
                                            'option_key' => $option->option_key,
                                            'option_text' => $option->option_text,
                                        ];
                                    })->toArray(),
                                    'items' => $items->isNotEmpty() ? $items->toArray() : null,
                                ];

                                if (!$isStudent) {
                                    $correctAnswers = json_decode($mainQuestion->correct_answers, true);
                                    $mainQuestionData['correct_answers'] = is_array($correctAnswers) && count($correctAnswers) === 1 ? $correctAnswers[0] : ($correctAnswers ?? []);
                                    $mainQuestionData['breakdown'] = $mainQuestion->breakdowns->map(function ($breakdown) {
                                        $highlights = $breakdown->highlightSegments->map(function ($highlight) {
                                            return [
                                                'start_char_index' => $highlight->start_char_index,
                                                'end_char_index' => $highlight->end_char_index,
                                            ];
                                        })->toArray();
                                        return [
                                            'explanation' => $breakdown->explanation,
                                            'has_highlight' => $breakdown->has_highlight,
                                            'highlights' => is_array($highlights) && count($highlights) === 1 ? $highlights[0] : $highlights,
                                        ];
                                    })->first();
                                }

                                return [
                                    'instruction' => $group->instruction,
                                    'questions' => [$mainQuestionData],
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            });

            return response()->json([
                'message' => 'Reading tests retrieved successfully',
                'data' => $tests,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve reading tests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = ValidationHelper::readingTest($request->all());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            // Create the test
            $test = Test::create([
                'id' => (string) Str::uuid(),
                'creator_id' => auth()->id(),
                'type' => $validated['type'],
                'difficulty' => $validated['difficulty'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'test_type' => $validated['test_type'] ?? 'single',
                'timer_mode' => $validated['timer_mode'] ?? 'none',
                'timer_settings' => $validated['timer_settings'] ?? null,
                'allow_repetition' => $validated['allow_repetition'] ?? false,
                'max_repetition_count' => $validated['max_repetition_count'] ?? null,
                'is_public' => $validated['is_public'] ?? false,
                'is_published' => $validated['is_published'] ?? true,
                'settings' => $validated['settings'] ?? null,
            ]);

            // Process each passage
            foreach ($validated['passages'] as $passageIndex => $passageData) {
                $passage = Passage::create([
                    'id' => (string) Str::uuid(),
                    'test_id' => $test->id,
                    'title' => $passageData['title'] ?? null,
                    'description' => $passageData['description'] ?? null,
                ]);

                foreach ($passageData['question_groups'] as $groupIndex => $groupData) {
                    $questionGroup = QuestionGroup::create([
                        'id' => (string) Str::uuid(),
                        'passage_id' => $passage->id,
                        'instruction' => $groupData['instruction'] ?? null,
                    ]);

                    foreach ($groupData['questions'] as $questionIndex => $questionData) {
                        $imageKey = "passages.{$passageIndex}.question_groups.{$groupIndex}.questions.{$questionIndex}.question_data.images";
                        $newImagePaths = [];
                        if ($request->hasFile($imageKey)) {
                            foreach ($request->file($imageKey) as $image) {
                                $newImagePaths[] = FileUploadHelper::upload($image, 'question_images');
                            }
                        }

                        $questionDataArray = $questionData['question_data'] ?? [];
                        unset($questionDataArray['images']);

                        $question = TestQuestion::create([
                            'id' => (string) Str::uuid(),
                            'question_group_id' => $questionGroup->id,
                            'question_type' => $questionData['question_type'],
                            'question_number' => $questionData['question_number'] ?? null,
                            'question_text' => $questionData['question_text'] ?? null,
                            'question_data' => array_merge(
                                $questionDataArray,
                                $newImagePaths ? ['image_path' => $newImagePaths] : []
                            ),
                            'correct_answers' => json_encode($questionData['correct_answers'] ?? []),
                            'points_value' => $questionData['points_value'] ?? 0,
                        ]);

                        if (isset($questionData['options'])) {
                            foreach ($questionData['options'] as $optionData) {
                                QuestionOption::create([
                                    'id' => (string) Str::uuid(),
                                    'question_id' => $question->id,
                                    'option_key' => $optionData['option_key'] ?? null,
                                    'option_text' => $optionData['option_text'] ?? null,
                                ]);
                            }
                        }

                        // Handle items if present (for types like Matching Heading)
                        if (isset($questionData['items'])) {
                            foreach ($questionData['items'] as $itemIndex => $itemData) {
                                $subQuestion = TestQuestion::create([
                                    'id' => (string) Str::uuid(),
                                    'question_group_id' => $questionGroup->id,
                                    'question_type' => $questionData['question_type'],
                                    'question_number' => $itemData['question_number'] ?? null,
                                    'question_text' => null,
                                    'question_data' => $itemData['question_data'] ?? [],
                                    'correct_answers' => json_encode($itemData['correct_answers'] ?? []),
                                ]);

                                if (isset($itemData['options'])) {
                                    foreach ($itemData['options'] as $optionData) {
                                        QuestionOption::create([
                                            'id' => (string) Str::uuid(),
                                            'question_id' => $subQuestion->id,
                                            'option_key' => $optionData['option_key'] ?? null,
                                            'option_text' => $optionData['option_text'] ?? null,
                                        ]);
                                    }
                                }
                            }

                            // Breakdown is attached to the main question
                            if (isset($questionData['breakdown'])) {
                                $breakdown = QuestionBreakdown::create([
                                    'id' => (string) Str::uuid(),
                                    'question_id' => $question->id,
                                    'explanation' => $questionData['breakdown']['explanation'] ?? null,
                                    'has_highlight' => $questionData['breakdown']['has_highlight'] ?? false,
                                ]);

                                if (isset($questionData['breakdown']['highlights'])) {
                                    foreach ($questionData['breakdown']['highlights'] as $highlightData) {
                                        HighlightSegment::create([
                                            'id' => (string) Str::uuid(),
                                            'breakdown_id' => $breakdown->id,
                                            'start_char_index' => $highlightData['start_char_index'] ?? null,
                                            'end_char_index' => $highlightData['end_char_index'] ?? null,
                                        ]);
                                    }
                                }
                            }
                        } else {
                            if (isset($questionData['breakdown'])) {
                                $breakdown = QuestionBreakdown::create([
                                    'id' => (string) Str::uuid(),
                                    'question_id' => $question->id,
                                    'explanation' => $questionData['breakdown']['explanation'] ?? null,
                                    'has_highlight' => $questionData['breakdown']['has_highlight'] ?? false,
                                ]);

                                if (isset($questionData['breakdown']['highlights'])) {
                                    foreach ($questionData['breakdown']['highlights'] as $highlightData) {
                                        HighlightSegment::create([
                                            'id' => (string) Str::uuid(),
                                            'breakdown_id' => $breakdown->id,
                                            'start_char_index' => $highlightData['start_char_index'] ?? null,
                                            'end_char_index' => $highlightData['end_char_index'] ?? null,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Reading test created successfully',
                'test_id' => $test->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create reading test',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $query = Test::query()->where('type', 'reading')
                ->where('id', $id)
                ->with([
                    'passages.questionGroups.questions.options',
                    'passages.questionGroups.questions.breakdowns.highlightSegments',
                    'creator'
                ]);

            // Apply role-based access control
            if ($user->role === 'admin') {
                // Admins can access any test
            } elseif ($user->role === 'student') {
                // Students can only access published tests
                $query->where('is_published', true);
            } else {
                // Non-admin, non-student users (e.g., teachers/creators) can only access their own tests
                $query->where('creator_id', $user->id);
            }

            $test = $query->first();

            // Check if test exists and is accessible
            if (!$test) {
                return response()->json([
                    'message' => 'Test not found or unauthorized access',
                ], 404);
            }

            $isStudent = $user->role === 'student';
            $testData = [
                'id' => $test->id,
                'creator_id' => $test->creator_id,
                'creator_name' => $test->creator ? $test->creator->name : null,
                'test_type' => $test->test_type,
                'type' => $test->type,
                'difficulty' => $test->difficulty,
                'title' => $test->title,
                'description' => $test->description,
                'timer_mode' => $test->timer_mode,
                'timer_settings' => $test->timer_settings,
                'allow_repetition' => $test->allow_repetition,
                'max_repetition_count' => $test->max_repetition_count,
                'is_public' => $test->is_public,
                'is_published' => $test->is_published,
                'settings' => $test->settings,
                'created_at' => $test->created_at,
                'updated_at' => $test->updated_at,
                'passages' => $test->passages->map(function ($passage) use ($isStudent) {
                    return [
                        'passage_id' => $passage->id,
                        'title' => $passage->title,
                        'description' => $passage->description,
                        'question_groups' => $passage->questionGroups->map(function ($group) use ($isStudent) {
                            $questions = $group->questions;

                            // Define the main question: the one without a dot (.)
                            $mainQuestion = $questions->first(function ($q) {
                                return strpos((string)$q->question_number, '.') === false;
                            });

                            // Fallback if there is no explicit main (e.g., all use 2.1, 2.2)
                            if (!$mainQuestion) {
                                $mainQuestion = $questions->sortBy('question_number')->first();
                            }

                            if (!$mainQuestion || $mainQuestion->question_type !== 'Matching Heading') {
                                return [
                                    'instruction' => $group->instruction,
                                    'questions' => $questions->map(function ($question) use ($isStudent) {
                                        $questionData = [
                                            'question_id' => $question->id,
                                            'question_type' => $question->question_type,
                                            'question_number' => $question->question_number,
                                            'question_text' => $question->question_text,
                                            'question_data' => $question->question_data,
                                            'points_value' => $question->points_value,
                                            'options' => $question->options->map(function ($option) {
                                                return [
                                                    'option_key' => $option->option_key,
                                                    'option_text' => $option->option_text,
                                                ];
                                            })->toArray(),
                                        ];

                                        if (!$isStudent) {
                                            $correctAnswers = $question->correct_answers;
                                            $decodedAnswers = is_array($correctAnswers) ? $correctAnswers : json_decode($correctAnswers, true) ?? [];
                                            $questionData['correct_answers'] = is_array($decodedAnswers) && count($decodedAnswers) === 1 ? $decodedAnswers[0] : $decodedAnswers;
                                            $questionData['breakdown'] = $question->breakdowns->map(function ($breakdown) {
                                                $highlights = $breakdown->highlightSegments->map(function ($highlight) {
                                                    return [
                                                        'start_char_index' => $highlight->start_char_index,
                                                        'end_char_index' => $highlight->end_char_index,
                                                    ];
                                                })->toArray();
                                                return [
                                                    'explanation' => $breakdown->explanation,
                                                    'has_highlight' => $breakdown->has_highlight,
                                                    'highlights' => is_array($highlights) && count($highlights) === 1 ? $highlights[0] : $highlights,
                                                ];
                                            })->first();
                                        }

                                        return $questionData;
                                    })->toArray(),
                                ];
                            }

                            // Group items under the main question
                            $items = $questions->filter(function ($q) use ($mainQuestion) {
                                return $q->id !== $mainQuestion->id && strpos((string)$q->question_number, '.') !== false;
                            })->map(function ($item) use ($isStudent) {
                                $itemData = [
                                    'question_id' => $item->id,
                                    'question_number' => $item->question_number,
                                ];

                                if (!$isStudent) {
                                    $correctAnswers = json_decode($item->correct_answers, true);
                                    $itemData['correct_answers'] = is_array($correctAnswers) && count($correctAnswers) === 1 ? $correctAnswers[0] : ($correctAnswers ?? []);
                                }

                                return $itemData;
                            })->values();

                            $mainQuestionData = [
                                'question_id' => $mainQuestion->id,
                                'question_type' => $mainQuestion->question_type,
                                'question_number' => $mainQuestion->question_number,
                                'question_data' => $mainQuestion->question_data,
                                'points_value' => $mainQuestion->points_value,
                                'options' => $mainQuestion->options->map(function ($option) {
                                    return [
                                        'option_key' => $option->option_key,
                                        'option_text' => $option->option_text,
                                    ];
                                })->toArray(),
                                'items' => $items->isNotEmpty() ? $items->toArray() : null,
                            ];

                            if (!$isStudent) {
                                $correctAnswers = json_decode($mainQuestion->correct_answers, true);
                                $mainQuestionData['correct_answers'] = is_array($correctAnswers) && count($correctAnswers) === 1 ? $correctAnswers[0] : ($correctAnswers ?? []);
                                $mainQuestionData['breakdown'] = $mainQuestion->breakdowns->map(function ($breakdown) {
                                    $highlights = $breakdown->highlightSegments->map(function ($highlight) {
                                        return [
                                            'start_char_index' => $highlight->start_char_index,
                                            'end_char_index' => $highlight->end_char_index,
                                        ];
                                    })->toArray();
                                    return [
                                        'explanation' => $breakdown->explanation,
                                        'has_highlight' => $breakdown->has_highlight,
                                        'highlights' => is_array($highlights) && count($highlights) === 1 ? $highlights[0] : $highlights,
                                    ];
                                })->first();
                            }

                            return [
                                'instruction' => $group->instruction,
                                'questions' => [$mainQuestionData],
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];

            return response()->json([
                'message' => 'Reading test retrieved successfully',
                'data' => $testData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve reading test',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $test = Test::find($id);
        if (!$test) {
            return response()->json(['message' => 'Test not found'], 404);
        }

        if ($test->creator_id !== auth()->user()->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to update this test'], 403);
        }

        $validator = ValidationHelper::readingTest($request->all());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            // Update main test
            $test->update([
                'difficulty' => $validated['difficulty'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'test_type' => $validated['test_type'] ?? $test->test_type,
                'timer_mode' => $validated['timer_mode'] ?? $test->timer_mode,
                'timer_settings' => $validated['timer_settings'] ?? null,
                'allow_repetition' => $validated['allow_repetition'] ?? false,
                'max_repetition_count' => $validated['max_repetition_count'] ?? null,
                'is_public' => $validated['is_public'] ?? false,
                'is_published' => $validated['is_published'] ?? $test->is_published,
                'settings' => $validated['settings'] ?? $test->settings,
            ]);

            $existingPassageIds = [];

            foreach ($validated['passages'] as $pIndex => $pData) {
                $passage = Passage::updateOrCreate(
                    ['id' => $pData['id'] ?? null],
                    [
                        'id' => $pData['id'] ?? (string) Str::uuid(),
                        'test_id' => $test->id,
                        'title' => $pData['title'] ?? null,
                        'description' => $pData['description'] ?? null,
                    ]
                );
                $existingPassageIds[] = $passage->id;

                $existingGroupIds = [];

                foreach ($pData['question_groups'] as $gIndex => $gData) {
                    $group = QuestionGroup::updateOrCreate(
                        ['id' => $gData['id'] ?? null],
                        [
                            'id' => $gData['id'] ?? (string) Str::uuid(),
                            'passage_id' => $passage->id,
                            'instruction' => $gData['instruction'] ?? null,
                        ]
                    );
                    $existingGroupIds[] = $group->id;

                    $existingQuestionIds = [];

                    foreach ($gData['questions'] as $qIndex => $qData) {
                        $imageKey = "passages.$pIndex.question_groups.$gIndex.questions.$qIndex.question_data.images";

                        $question = TestQuestion::updateOrCreate(
                            ['id' => $qData['id'] ?? null],
                            [
                                'id' => $qData['id'] ?? (string) Str::uuid(),
                                'question_group_id' => $group->id,
                                'question_type' => $qData['question_type'],
                                'question_number' => $qData['question_number'] ?? null,
                                'question_text' => $qData['question_text'] ?? null,
                                'points_value' => $qData['points_value'] ?? 0,
                            ]
                        );

                        // Get existing data
                        $currentData = is_array($question->question_data) ? $question->question_data : [];
                        $currentPaths = $currentData['image_path'] ?? [];

                        // Handle new uploads
                        $newPaths = [];
                        if ($request->hasFile($imageKey)) {
                            // Delete old images
                            foreach ($currentPaths as $old) {
                                FileUploadHelper::delete(str_replace('/storage/', '', $old));
                            }
                            $currentPaths = [];
                            foreach ($request->file($imageKey) as $img) {
                                $newPaths[] = FileUploadHelper::upload($img, 'question_images');
                            }
                        }

                        // Handle remove_images
                        $removeImages = $qData['question_data']['remove_images'] ?? [];
                        if ($removeImages && !$newPaths) {
                            foreach ($removeImages as $rm) {
                                FileUploadHelper::delete(str_replace('/storage/', '', $rm));
                            }
                            $currentPaths = array_values(array_diff($currentPaths, $removeImages));
                        }

                        $finalPaths = $newPaths ?: $currentPaths;

                        $newQData = $qData['question_data'] ?? [];
                        unset($newQData['images'], $newQData['remove_images']);

                        $question->update([
                            'question_data' => array_merge($newQData, $finalPaths ? ['image_path' => $finalPaths] : []),
                            'correct_answers' => json_encode($qData['correct_answers'] ?? ($question->correct_answers ? json_decode($question->correct_answers, true) : [])),
                        ]);

                        $existingQuestionIds[] = $question->id;

                        // Handle options
                        if (isset($qData['options'])) {
                            $optionIds = [];
                            foreach ($qData['options'] as $oData) {
                                $opt = QuestionOption::updateOrCreate(
                                    ['id' => $oData['id'] ?? null],
                                    [
                                        'id' => $oData['id'] ?? (string) Str::uuid(),
                                        'question_id' => $question->id,
                                        'option_key' => $oData['option_key'],
                                        'option_text' => $oData['option_text'] ?? null,
                                    ]
                                );
                                $optionIds[] = $opt->id;
                            }
                            QuestionOption::where('question_id', $question->id)
                                ->whereNotIn('id', $optionIds)
                                ->delete();
                        }

                        // Handle items if present (for types like Matching Heading)
                        if (isset($qData['items']) || isset($qData['remove_items'])) {
                            $subQuestionIds = [];

                            // Update or create items
                            if (isset($qData['items'])) {
                                foreach ($qData['items'] as $itemData) {
                                    $subQuestion = TestQuestion::updateOrCreate(
                                        ['id' => $itemData['id'] ?? null],
                                        [
                                            'id' => $itemData['id'] ?? (string) Str::uuid(),
                                            'question_group_id' => $group->id,
                                            'question_type' => $qData['question_type'],
                                            'question_number' => $itemData['question_number'] ?? null,
                                            'question_text' => null,
                                            'question_data' => $itemData['question_data'] ?? [],
                                            'correct_answers' => json_encode($itemData['correct_answers'] ?? []),
                                        ]
                                    );
                                    $subQuestionIds[] = $subQuestion->id;
                                }
                            }

                            // Delete items explicitly marked for removal
                            if (isset($qData['remove_items']) && is_array($qData['remove_items'])) {
                                TestQuestion::whereIn('id', $qData['remove_items'])
                                    ->where('question_group_id', $group->id)
                                    ->delete();
                            }
                        }

                        // Handle breakdown for main question
                        if (isset($qData['breakdown'])) {
                            $breakdown = QuestionBreakdown::updateOrCreate(
                                ['question_id' => $question->id],
                                [
                                    'id' => $qData['breakdown']['id'] ?? (string) Str::uuid(),
                                    'explanation' => $qData['breakdown']['explanation'] ?? null,
                                    'has_highlight' => $qData['breakdown']['has_highlight'] ?? false,
                                ]
                            );

                            if (isset($qData['breakdown']['highlights'])) {
                                $highlightIds = [];
                                foreach ($qData['breakdown']['highlights'] as $hData) {
                                    $h = HighlightSegment::updateOrCreate(
                                        ['id' => $hData['id'] ?? null],
                                        [
                                            'id' => $hData['id'] ?? (string) Str::uuid(),
                                            'breakdown_id' => $breakdown->id,
                                            'start_char_index' => $hData['start_char_index'],
                                            'end_char_index' => $hData['end_char_index'],
                                        ]
                                    );
                                    $highlightIds[] = $h->id;
                                }
                                HighlightSegment::where('breakdown_id', $breakdown->id)
                                    ->whereNotIn('id', $highlightIds)
                                    ->delete();
                            }
                        }
                    }

                    // Delete main questions not included in the request
                    TestQuestion::where('question_group_id', $group->id)
                        ->whereNotIn('id', $existingQuestionIds)
                        ->whereDoesntHave('questionGroup', function ($query) {
                            $query->where('question_type', 'Matching Heading');
                        })
                        ->delete();
                }

                QuestionGroup::where('passage_id', $passage->id)
                    ->whereNotIn('id', $existingGroupIds)
                    ->delete();
            }

            Passage::where('test_id', $test->id)
                ->whereNotIn('id', $existingPassageIds)
                ->delete();

            DB::commit();
            return response()->json(['message' => 'Reading test updated successfully', 'test_id' => $test->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update reading test', 'error' => $e->getMessage()], 500);
        }
    }


    public function deletePassage($passageId)
    {
        $passage = Passage::find($passageId);
        if (!$passage) {
            return response()->json(['message' => 'Passage not found'], 404);
        }

        $test = $passage->test;
        if ($test->creator_id !== auth()->user()->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to delete this passage'], 403);
        }

        try {
            DB::beginTransaction();

            // Get all the questions in the passage
            $questions = TestQuestion::whereHas('questionGroup', function ($query) use ($passageId) {
                $query->where('passage_id', $passageId);
            })->get();

            // Remove related images from each question
            foreach ($questions as $question) {
                if (isset($question->question_data['image_path'])) {
                    $imagePaths = is_array($question->question_data['image_path'])
                        ? $question->question_data['image_path']
                        : [$question->question_data['image_path']];
                    foreach ($imagePaths as $path) {
                        $deletePath = str_replace('/storage/', '', $path);
                        FileUploadHelper::delete($deletePath);
                    }
                }
            }

            // Delete passage (cascade will delete question groups and questions)
            $passage->delete();

            DB::commit();

            return response()->json([
                'message' => 'Passage and its contents deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete passage',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteQuestion($questionId)
    {
        $question = TestQuestion::find($questionId);
        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $questionGroup = $question->questionGroup;
        $test = $questionGroup->passage->test;

        if ($test->creator_id !== auth()->user()->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to delete this question'], 403);
        }

        try {
            DB::beginTransaction();

            // Delete associated images if they exist
            if (isset($question->question_data['image_path'])) {
                $imagePaths = is_array($question->question_data['image_path'])
                    ? $question->question_data['image_path']
                    : [$question->question_data['image_path']];
                foreach ($imagePaths as $path) {
                    $deletePath = str_replace('/storage/', '', $path);
                    FileUploadHelper::delete($deletePath);
                }
            }

            // Delete the question (cascades to options, breakdowns, highlights via onDelete('cascade'))
            $question->delete();

            // Check if the question group has any remaining questions
            $remainingQuestions = TestQuestion::where('question_group_id', $questionGroup->id)->count();
            $deletedGroup = false;
            if ($remainingQuestions === 0) {
                $questionGroup->delete();
                $deletedGroup = true;
            }

            DB::commit();

            return response()->json([
                'message' => $deletedGroup
                    ? 'Question and its empty question group deleted successfully'
                    : 'Question deleted successfully',
                'question_group_deleted' => $deletedGroup,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $test = Test::find($id);
        if (!$test) {
            return response()->json(['message' => 'Test not found'], 404);
        }

        if ($test->creator_id !== auth()->user()->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to delete this test'], 403);
        }

        try {
            DB::beginTransaction();

            $questions = TestQuestion::whereHas('questionGroup.passage', function ($query) use ($id) {
                $query->where('test_id', $id);
            })->get();

            foreach ($questions as $question) {
                if (isset($question->question_data['image_path'])) {
                    $imagePaths = is_array($question->question_data['image_path'])
                        ? $question->question_data['image_path']
                        : [$question->question_data['image_path']];
                    foreach ($imagePaths as $path) {
                        $deletePath = str_replace('/storage/', '', $path);
                        FileUploadHelper::delete($deletePath);
                    }
                }
            }

            $test->delete();

            DB::commit();

            return response()->json([
                'message' => 'Reading test deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete reading test',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
