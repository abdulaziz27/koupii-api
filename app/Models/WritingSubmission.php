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
 * @property string $original_content
 * @property string $revised_content
 * @property int $word_count
 * @property array $writing_analytics
 * @property string $submitted_at
 */
class WritingSubmission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'writing_submissions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'question_id',
        'user_id',
        'original_content',
        'revised_content',
        'word_count',
        'writing_analytics',
        'submitted_at',
    ];

    protected $casts = [
        'writing_analytics' => 'array',
        'submitted_at' => 'datetime',
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
