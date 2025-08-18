<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $audio_file_path
 * @property string|null $transcript_type
 * @property array|null $transcript
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Passage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'passages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'title',
        'description',
        'audio_file_path',
        'transcript_type',
        'transcript',
    ];

    protected $casts = [
        'transcript_type' => 'string',
        'transcript' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function questionGroups()
    {
        return $this->hasMany(QuestionGroup::class, 'passage_id');
    }
}
