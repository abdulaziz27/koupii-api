<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $session_id
 * @property string $user_id
 * @property float $grammatical_range_accuracy_score
 * @property float $lexical_resource_score
 * @property float $coherence_cohesion_score
 * @property float $task_response_score
 * @property float $overall_score
 * @property array $detailed_feedback
 * @property array $error_analysis
 * @property array $improvement_suggestions
 * @property string $suggested_revision
 * @property string $assessed_at
 */
class WritingAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'writing_assessments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'user_id',
        'grammatical_range_accuracy_score',
        'lexical_resource_score',
        'coherence_cohesion_score',
        'task_response_score',
        'overall_score',
        'detailed_feedback',
        'error_analysis',
        'improvement_suggestions',
        'suggested_revision',
        'assessed_at',
    ];

    protected $casts = [
        'grammatical_range_accuracy_score' => 'decimal:2',
        'lexical_resource_score' => 'decimal:2',
        'coherence_cohesion_score' => 'decimal:2',
        'task_response_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'detailed_feedback' => 'array',
        'error_analysis' => 'array',
        'assessed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
