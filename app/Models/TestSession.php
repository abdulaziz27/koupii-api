<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $test_id
 * @property string $assignment_id
 * @property string $attempt_number
 * @property string $test_type
 * @property array $session_data
 * @property string $status
 * @property string $started_at
 * @property string $last_activity_at
 * @property string $completed_at
 */
class TestSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_sessions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'test_id',
        'assignment_id',
        'attempt_number',
        'test_type',
        'session_data',
        'status',
        'started_at',
        'last_activity_at',
        'completed_at',
    ];

    protected $casts = [
        'session_data' => 'array',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function responses()
    {
        return $this->hasMany(QuestionResponse::class, 'session_id');
    }

    public function audioRecordings()
    {
        return $this->hasMany(AudioRecording::class, 'session_id');
    }

    public function writingSubmissions()
    {
        return $this->hasMany(WritingSubmission::class, 'session_id');
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class, 'session_id');
    }

    public function speakingAssessments()
    {
        return $this->hasMany(SpeakingAssessment::class, 'session_id');
    }
}
