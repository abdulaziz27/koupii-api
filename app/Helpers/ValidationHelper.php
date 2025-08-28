<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    public static function register($data)
    {
        return Validator::make(
            $data,
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'role' => 'required|in:teacher,student,admin',
            ],
            [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Email must be a valid email address',
                'email.unique' => 'Email already exists',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'role.required' => 'Role is required',
                'role.in' => 'Role must be teacher, student, or admin',
            ],
        );
    }

    public static function login($data)
    {
        return Validator::make(
            $data,
            [
                'email' => 'required|email',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email is required',
                'email.email' => 'Email must be a valid email address',
                'password.required' => 'Password is required',
            ],
        );
    }

    public static function profile($data)
    {
        return Validator::make(
            $data,
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . auth()->user()->id,
                'role' => 'required|in:teacher,student,admin',
                'avatar' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg|max:2048',
                'bio' => 'nullable|string',
            ],
            [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Email must be a valid email address',
                'email.unique' => 'Email already exists',
                'role.required' => 'Role is required',
                'role.in' => 'Role must be teacher, student, or admin',
                'avatar.file' => 'Avatar must be a file',
                'avatar.mimetypes' => 'Avatar must be a JPEG, PNG, or JPG file',
                'avatar.max' => 'Avatar size must be at most 2MB',
                'bio.string' => 'Bio must be a string',
            ]
        );
    }

    public static function vocabularyCategory($data)
    {
        return Validator::make(
            $data,
            [
                'name' => 'required|string|max:255',
                'color_code' => 'nullable|string|max:20',
            ],
            [
                'name.required' => 'Category name is required',
                'name.string' => 'Category name must be a string',
                'color_code.string' => 'Color code must be a string',
            ],
        );
    }

    public static function vocabulary($data)
    {
        return Validator::make(
            $data,
            [
                'category_id' => 'required|exists:vocabulary_categories,id',
                'word' => 'required|string|max:255',
                'translation' => 'required|string|max:255',
                'spelling' => 'nullable|string|max:255',
                'explanation' => 'nullable|string',
                'audio_file_path' => [
                    'nullable',
                    'file',
                    'max:2048',
                    function ($attribute, $value, $fail) {
                        if (!in_array(strtolower($value->getClientOriginalExtension()), ['mp3', 'wav', 'ogg'])) {
                            $fail('The file must be an audio file like MP3, WAV, or OGG.');
                        }
                    },
                ],
                'is_public' => 'boolean',
            ],
            [
                'category_id.required' => 'Category ID is required',
                'category_id.exists' => 'Category ID not found',
                'word.required' => 'Word is required',
                'translation.required' => 'Translation is required',
            ],
        );
    }

    public static function class($data, $isUpdate = false)
    {
        return Validator::make(
            $data,
            [
                'name' => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:255',
                'description' => 'nullable|string',
                'class_code' => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:50|unique:classes,class_code' . ($isUpdate ? ',' . ($data['id'] ?? 'NULL') : ''),
                'cover_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg|max:2048',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'Class name is required',
                'name.string' => 'Class name must be a string',
                'description.string' => 'Description must be a string',
                'cover_image.mimetypes' => 'Cover image must be a JPEG or PNG image',
                'cover_image.max' => 'Cover image size must be at most 2MB',
                'class_code.required' => 'Class code is required',
                'class_code.unique' => 'Class code already exists',
            ],
        );
    }

    public static function classEnrollment($data, $isUpdate = false)
    {
        return Validator::make(
            $data,
            [
                'status' => 'sometimes|in:active,inactive,pending',
                'enrolled_at' => 'nullable|date',
            ],
            [
                'status.in' => 'Status must be active, inactive, or pending',
            ],
        );
    }

    public static function classInvitation($data)
    {
        return Validator::make(
            $data,
            [
                'class_code' => 'required|exists:classes,class_code',
                'email' => 'required',
            ],
            [
                'class_code.required' => 'Class code is required',
                'class_code.exists' => 'Class code does not exist',
                'email.required' => 'Email is required',
            ],
        );
    }

    public static function changePassword($data)
    {
        return Validator::make(
            $data,
            [
                'current_password' => 'required|string',
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                ],
            ],
            [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'New password must be at least 8 characters',
                'new_password.confirmed' => 'New password confirmation does not match',
                'new_password.regex' => 'New password must include uppercase, lowercase, number, and special character',
            ]
        );
    }

    public static function readingTest($data)
    {
        return Validator::make(
            $data,
            [
                // General test information
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:reading,listening,speaking,writing', // Diperluas sesuai ERD
                'difficulty' => 'required|in:beginner,intermediate,advanced',
                'test_type' => 'required|in:single,final',
                'timer_mode' => 'nullable|in:countdown,countup,none',
                'timer_settings' => 'nullable|array',
                'timer_settings.hours' => 'nullable|integer|min:0',
                'timer_settings.minutes' => 'nullable|integer|min:0|max:59',
                'timer_settings.seconds' => 'nullable|integer|min:0|max:59',
                'allow_repetition' => 'nullable|boolean',
                'max_repetition_count' => 'nullable|integer|min:1',
                'is_public' => 'nullable|boolean',
                'is_published' => 'nullable|boolean',
                'settings' => 'nullable|array',

                // Passages (array of passages)
                'passages' => 'required|array|min:1',
                'passages.*.title' => 'nullable|string|max:255',
                'passages.*.description' => 'nullable|string',

                // Question groups within each passage
                'passages.*.question_groups' => 'required|array|min:1',
                'passages.*.question_groups.*.instruction' => 'nullable|string',

                // Questions within each question group
                'passages.*.question_groups.*.questions' => 'required|array|min:1',
                'passages.*.question_groups.*.questions.*.question_type' => 'required|string|max:255',
                'passages.*.question_groups.*.questions.*.question_number' => 'nullable|numeric|min:1',
                'passages.*.question_groups.*.questions.*.question_text' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.question_data' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.question_data.images.*' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
                'passages.*.question_groups.*.questions.*.question_data.remove_images.*' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.correct_answers' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.points_value' => 'nullable|numeric|min:0',
                'passages.*.question_groups.*.questions.*.options' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.options.*.option_key' => 'nullable|string|max:50',
                'passages.*.question_groups.*.questions.*.options.*.option_text' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.breakdown' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.breakdown.explanation' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.breakdown.has_highlight' => 'nullable|boolean',
                'passages.*.question_groups.*.questions.*.breakdown.highlights' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.start_char_index' => 'nullable|integer|min:0',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.end_char_index' => 'nullable|integer|min:0',

                // Support for sub-questions (items) like in Matching Heading
                'passages.*.question_groups.*.questions.*.items' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.items.*.question_number' => 'nullable|numeric|min:0.1',
                'passages.*.question_groups.*.questions.*.items.*.question_text' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.items.*.correct_answers' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.items.*.points_value' => 'nullable|numeric|min:0',
                'passages.*.question_groups.*.questions.*.items.*.options' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.items.*.options.*.option_key' => 'nullable|string|max:50',
                'passages.*.question_groups.*.questions.*.items.*.options.*.option_text' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.items.*.breakdown' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.items.*.breakdown.explanation' => 'nullable|string',
                'passages.*.question_groups.*.questions.*.items.*.breakdown.has_highlight' => 'nullable|boolean',
                'passages.*.question_groups.*.questions.*.items.*.breakdown.highlights' => 'nullable|array',
                'passages.*.question_groups.*.questions.*.items.*.breakdown.highlights.*.start_char_index' => 'nullable|integer|min:0',
                'passages.*.question_groups.*.questions.*.items.*.breakdown.highlights.*.end_char_index' => 'nullable|integer|min:0',
            ],
            [
                'title.required' => 'Test title is required.',
                'title.max' => 'Test title cannot exceed 255 characters.',
                'type.required' => 'Test type is required.',
                'type.in' => 'Test type must be one of: reading, listening, speaking, writing.',
                'difficulty.required' => 'Difficulty level is required.',
                'difficulty.in' => 'Difficulty must be one of: beginner, intermediate, advanced.',
                'test_type.required' => 'Test type (single/final) is required.',
                'test_type.in' => 'Test type must be either "single" or "final".',
                'timer_mode.in' => 'Timer mode must be one of: countdown, countup, none.',
                'timer_settings.hours.integer' => 'Timer hours must be an integer.',
                'timer_settings.hours.min' => 'Timer hours cannot be negative.',
                'timer_settings.minutes.integer' => 'Timer minutes must be an integer.',
                'timer_settings.minutes.min' => 'Timer minutes cannot be negative.',
                'timer_settings.minutes.max' => 'Timer minutes cannot exceed 59.',
                'timer_settings.seconds.integer' => 'Timer seconds must be an integer.',
                'timer_settings.seconds.min' => 'Timer seconds cannot be negative.',
                'timer_settings.seconds.max' => 'Timer seconds cannot exceed 59.',
                'max_repetition_count.integer' => 'Max repetition count must be an integer.',
                'max_repetition_count.min' => 'Max repetition count must be at least 1.',
                'passages.required' => 'At least one passage is required.',
                'passages.min' => 'At least one passage is required.',
                'passages.*.title.max' => 'Passage title cannot exceed 255 characters.',
                'passages.*.question_groups.required' => 'At least one question group is required per passage.',
                'passages.*.question_groups.min' => 'At least one question group is required per passage.',
                'passages.*.question_groups.*.questions.required' => 'At least one question is required per question group.',
                'passages.*.question_groups.*.questions.min' => 'At least one question is required per question group.',
                'passages.*.question_groups.*.questions.*.question_type.required' => 'Question type is required for each question.',
                'passages.*.question_groups.*.questions.*.question_type.max' => 'Question type cannot exceed 255 characters.',
                'passages.*.question_groups.*.questions.*.question_number.numeric' => 'Question number must be a number.',
                'passages.*.question_groups.*.questions.*.question_number.min' => 'Question number must be at least 1.',
                'passages.*.question_groups.*.questions.*.question_data.images.*.file' => 'The uploaded file must be an image.',
                'passages.*.question_groups.*.questions.*.question_data.images.*.mimes' => 'The image must be a JPEG, PNG, or JPG file.',
                'passages.*.question_groups.*.questions.*.question_data.images.*.max' => 'The image size cannot exceed 2MB.',
                'passages.*.question_groups.*.questions.*.options.*.option_key.max' => 'Option key cannot exceed 50 characters.',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.start_char_index.integer' => 'Start character index must be an integer.',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.start_char_index.min' => 'Start character index cannot be negative.',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.end_char_index.integer' => 'End character index must be an integer.',
                'passages.*.question_groups.*.questions.*.breakdown.highlights.*.end_char_index.min' => 'End character index cannot be negative.',
            ]
        );
    }
}
