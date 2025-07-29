<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 *
 * @property string $id
 * @property string $test_id
 * @property string $question_type_id
 * @property string $instruction_text
 */
class QuestionInstruction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_instructions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'question_type_id',
        'instruction_text',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function questions()
    {
        return $this->hasMany(TestQuestion::class);
    }
}
