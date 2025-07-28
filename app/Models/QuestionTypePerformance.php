<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $question_type_name
 * @property string $skill_category
 * @property int $total_attempts
 * @property int $correct_answers
 * @property float $accuracy_percentage
 * @property array $performance_history
 * @property string $last_attempt
 */
class QuestionTypePerformance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_type_performance';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'question_type_name',
        'skill_category',
        'total_attempts',
        'correct_answers',
        'accuracy_percentage',
        'performance_history',
        'last_attempt',
    ];

    protected $casts = [
        'accuracy_percentage' => 'decimal:2',
        'performance_history' => 'array',
        'last_attempt' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
