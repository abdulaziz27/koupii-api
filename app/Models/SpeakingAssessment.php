<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $session_id
 * @property string $user_id
 * @property float $fluency_coherence_score
 * @property float $lexical_resource_score
 * @property float $grammatical_range_accuracy_score
 * @property float $pronunciation_score
 * @property float $overall_score
 * @property array $detailed_feedback
 * @property array $grammar_analysis
 * @property array $improvement_suggestions
 * @property string $assessed_at
 */
class SpeakingAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'speaking_assessments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'user_id',
        'fluency_coherence_score',
        'lexical_resource_score',
        'grammatical_range_accuracy_score',
        'pronunciation_score',
        'overall_score',
        'detailed_feedback',
        'grammar_analysis',
        'improvement_suggestions',
        'assessed_at',
    ];

    protected $casts = [
        'fluency_coherence_score' => 'decimal:2',
        'lexical_resource_score' => 'decimal:2',
        'grammatical_range_accuracy_score' => 'decimal:2',
        'pronunciation_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'detailed_feedback' => 'array',
        'grammar_analysis' => 'array',
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
