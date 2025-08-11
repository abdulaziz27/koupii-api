<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $question_type_id
 * @property int|null $question_number
 * @property string|null $question_text
 * @property array|null $question_data
 * @property array|null $correct_answers
 * @property float $points_value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TestQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_questions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'question_type_id',
        'question_number',
        'question_text',
        'question_data',
        'correct_answers',
        'points_value',
    ];

    protected $casts = [
        'question_data' => 'array',
        'correct_answers' => 'array',
        'points_value' => 'float',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    public function studentAttempts()
    {
        return $this->hasMany(StudentQuestionAttempt::class, 'question_id');
    }

    public function breakdowns()
    {
        return $this->hasMany(QuestionBreakdown::class, 'question_id');
    }

    public function classAnalytics()
    {
        return $this->hasMany(ClassAnalytic::class, 'most_mistaken_question_id');
    }

    public function testReports()
    {
        return $this->hasMany(TestReport::class, 'most_mistaken_question_id');
    }
}
