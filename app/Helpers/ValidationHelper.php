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
                'audio_file_path' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/mpga,audio/wav,audio/ogg|max:2048',
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
}
