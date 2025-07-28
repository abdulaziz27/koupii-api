<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $session_id
 * @property string $question_id
 * @property string $user_id
 * @property array $student_answer
 * @property bool $is_correct
 * @property float $points_earned
 * @property string $answered_at
 */
class QuestionResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_responses';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'question_id',
        'user_id',
        'student_answer',
        'is_correct',
        'points_earned',
        'answered_at',
    ];

    protected $casts = [
        'student_answer' => 'array',
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'answered_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
