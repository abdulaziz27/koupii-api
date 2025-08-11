<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $module
 * @property array|null $structure
 * @property array|null $scoring_rules
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class QuestionType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_types';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'module',
        'structure',
        'scoring_rules',
        'is_active',
    ];

    protected $casts = [
        'module' => 'string',
        'structure' => 'array',
        'scoring_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class, 'question_type_id');
    }

    public function readingQuestionGroups()
    {
        return $this->hasMany(ReadingQuestionGroup::class, 'question_type_id');
    }

    public function listeningQuestionGroups()
    {
        return $this->hasMany(ListeningQuestionGroup::class, 'question_type_id');
    }

    public function studentDashboardMetricsWeakest()
    {
        return $this->hasMany(StudentDashboardMetric::class, 'weakest_question_type_id');
    }

    public function studentDashboardMetricsBest()
    {
        return $this->hasMany(StudentDashboardMetric::class, 'best_question_type_id');
    }
}
