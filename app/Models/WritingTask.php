<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $task_type
 * @property string|null $topic
 * @property string|null $prompt
 * @property int|null $suggest_time_minutes
 * @property int|null $min_word_count
 * @property string|null $sample_answer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class WritingTask extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'writing_tasks';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'task_type',
        'topic',
        'prompt',
        'suggest_time_minutes',
        'min_word_count',
        'sample_answer',
    ];

    protected $casts = [
        'task_type' => 'string',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
