<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $student_id
 * @property int $tasks_completed
 * @property int $total_time_spent_seconds
 * @property float|null $average_score
 * @property string|null $weakest_question_type_id
 * @property string|null $best_question_type_id
 * @property array|null $reading_progress_by_section
 * @property \Carbon\Carbon|null $metric_month
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StudentDashboardMetric extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_dashboard_metrics';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'tasks_completed',
        'total_time_spent_seconds',
        'average_score',
        'weakest_question_type_id',
        'best_question_type_id',
        'reading_progress_by_section',
        'metric_month',
    ];

    protected $casts = [
        'average_score' => 'float',
        'reading_progress_by_section' => 'array',
        'metric_month' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function weakestQuestionType()
    {
        return $this->belongsTo(QuestionType::class, 'weakest_question_type_id');
    }

    public function bestQuestionType()
    {
        return $this->belongsTo(QuestionType::class, 'best_question_type_id');
    }
}
