<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $passage_id
 * @property string $question_type_id
 * @property string $question_number
 * @property string $question_group
 * @property string $question_text
 * @property string $question_data
 * @property string $correct_answers
 * @property string $points_value
 * @property string $explanation
 * @property string $audio_start_time
 * @property string $audio_end_time
 */
class TestQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_questions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'passage_id',
        'question_type_id',
        'question_number',
        'question_group',
        'question_text',
        'question_data',
        'correct_answers',
        'points_value',
        'explanation',
        'audio_start_time',
        'audio_end_time',
    ];

    protected $casts = [
        'question_data' => 'array',
        'correct_answers' => 'array',
        'points_value' => 'decimal:2',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function passage()
    {
        return $this->belongsTo(TestPassage::class, 'passage_id');
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    public function responses()
    {
        return $this->hasMany(QuestionResponse::class, 'question_id');
    }

    public function audioRecordings()
    {
        return $this->hasMany(AudioRecording::class, 'question_id');
    }

    public function instruction()
    {
        return $this->belongsTo(QuestionInstruction::class, 'question_instruction_id');
    }
}
