<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $speaking_topic_id
 * @property int $question_number
 * @property string $question_text
 * @property int $time_limit_seconds
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SpeakingQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'speaking_questions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'speaking_topic_id',
        'question_number',
        'question_text',
        'time_limit_seconds',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'question_text' => 'string',
        'time_limit_seconds' => 'integer',
    ];

    public function speakingTopic()
    {
        return $this->belongsTo(SpeakingTopic::class, 'speaking_topic_id');
    }
}
