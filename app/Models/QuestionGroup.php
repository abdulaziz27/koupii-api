<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $passage_id
 * @property string $question_type
 * @property string|null $instruction
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class QuestionGroup extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_groups';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'passage_id',
        'question_type',
        'instruction',
    ];

    protected $casts = [
        'question_type' => 'string',
    ];

    public function passage()
    {
        return $this->belongsTo(Passage::class, 'passage_id');
    }

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'question_group_id');
    }
}
