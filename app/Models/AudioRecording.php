<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $session_id
 * @property string $question_id
 * @property string $user_id
 * @property string $audio_file_path
 * @property string $audio_format
 * @property int $duration_seconds
 * @property string $transcript
 * @property array $audio_analysis
 * @property string $recorded_at
 */
class AudioRecording extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'audio_recordings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'question_id',
        'user_id',
        'audio_file_path',
        'audio_format',
        'duration_seconds',
        'transcript',
        'audio_analysis',
        'recorded_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'audio_analysis' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
