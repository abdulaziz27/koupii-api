<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $student_assignment_id
 * @property string $question_id
 * @property array|null $selected_answer
 * @property bool|null $is_correct
 * @property float|null $points_earned
 * @property int|null $time_spent_seconds
 * @property \Carbon\Carbon|null $created_at
 */
class StudentQuestionAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_question_attempts';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_assignment_id',
        'question_id',
        'selected_answer',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
    ];

    protected $casts = [
        'selected_answer' => 'array',
        'is_correct' => 'boolean',
        'points_earned' => 'float',
    ];

    public function studentAssignment()
    {
        return $this->belongsTo(StudentAssignment::class, 'student_assignment_id');
    }

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}
