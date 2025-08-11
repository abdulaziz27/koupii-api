<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $passage_id
 * @property string $question_type_id
 * @property string|null $instruction
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReadingQuestionGroup extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'reading_question_groups';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'passage_id',
        'question_type_id',
        'instruction',
    ];

    public function passage()
    {
        return $this->belongsTo(ReadingPassage::class, 'passage_id');
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }
}
