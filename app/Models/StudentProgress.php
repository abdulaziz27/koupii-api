<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $skill_type
 * @property int $tasks_completed
 * @property int $total_time_spent_seconds
 * @property float $average_score
 * @property array $monthly_performance
 * @property array $weakest_question_types
 * @property array $improvement_trends
 * @property string $last_updated
 */
class StudentProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_progress';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'skill_type',
        'tasks_completed',
        'total_time_spent_seconds',
        'average_score',
        'monthly_performance',
        'weakest_question_types',
        'improvement_trends',
        'last_updated',
    ];

    protected $casts = [
        'average_score' => 'decimal:2',
        'monthly_performance' => 'array',
        'weakest_question_types' => 'array',
        'improvement_trends' => 'array',
        'last_updated' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
