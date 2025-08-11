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
 * @property int|null $duration_minutes
 * @property string|null $audio_file_path
 * @property string|null $transcript_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ListeningPassage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'listening_passages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'title',
        'description',
        'duration_minutes',
        'audio_file_path',
        'transcript_type',
    ];

    protected $casts = [
        'transcript_type' => 'string',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function transcripts()
    {
        return $this->hasMany(ListeningTranscript::class, 'passage_id');
    }

    public function dialogs()
    {
        return $this->hasMany(ListeningDialog::class, 'passage_id');
    }

    public function questionGroups()
    {
        return $this->hasMany(ListeningQuestionGroup::class, 'passage_id');
    }
}
