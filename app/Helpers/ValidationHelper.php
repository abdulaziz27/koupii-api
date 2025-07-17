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
                'avatar' => 'nullable|url',
                'bio' => 'nullable|string|max:1000',
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
                'avatar.url' => 'Avatar must be a valid URL',
                'bio.max' => 'Bio must be at most 1000 characters',
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
                'audio_file_path' => 'nullable|string|max:255',
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
                'cover_image' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'Class name is required',
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
                'class_id' => ($isUpdate ? 'sometimes|' : '') . 'required|exists:classes,id',
                'status' => 'required|in:active,inactive,pending',
                'enrolled_at' => 'nullable|date',
            ],
            [
                'class_id.required' => 'Class is required',
                'class_id.exists' => 'Class not found',
                'status.required' => 'Status is required',
                'status.in' => 'Status must be active, inactive, or pending',
            ],
        );
    }

    public static function classInvitation($data)
    {
        return Validator::make(
            $data,
            [
                'class_id' => 'required|exists:classes,id',
                'student_id' => 'required|exists:users,id',
                'email' => 'required|exists:users,email',
            ],
            [
                'class_id.required' => 'Class ID is required',
                'class_id.exists' => 'Class does not exist',
                'student_id.required' => 'Student ID is required',
                'student_id.exists' => 'Student does not exist',
                'email.required' => 'Email is required',
                'email.exists' => 'Email does not exist',
            ],
        );
    }
}
