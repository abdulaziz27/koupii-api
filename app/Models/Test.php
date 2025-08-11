<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $type
 * @property string $difficulty
 * @property string $title
 * @property string|null $description
 * @property int|null $time_limit_minutes
 * @property bool $allow_repetition
 * @property int|null $max_repetition_count
 * @property bool $is_public
 * @property bool $is_published
 * @property array|null $settings
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Test extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tests';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'creator_id',
        'type',
        'difficulty',
        'title',
        'description',
        'time_limit_minutes',
        'allow_repetition',
        'max_repetition_count',
        'is_public',
        'is_published',
        'settings',
    ];

    protected $casts = [
        'type' => 'string',
        'difficulty' => 'string',
        'allow_repetition' => 'boolean',
        'is_public' => 'boolean',
        'is_published' => 'boolean',
        'settings' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'test_id');
    }

    public function readingPassages()
    {
        return $this->hasMany(ReadingPassage::class, 'test_id');
    }

    public function listeningPassages()
    {
        return $this->hasMany(ListeningPassage::class, 'test_id');
    }

    public function speakingSections()
    {
        return $this->hasMany(SpeakingSection::class, 'test_id');
    }

    public function writingTasks()
    {
        return $this->hasMany(WritingTask::class, 'test_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'test_id');
    }

    public function testReports()
    {
        return $this->hasMany(TestReport::class, 'test_id');
    }
}
