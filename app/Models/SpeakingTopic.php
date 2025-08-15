<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $speaking_section_id
 * @property string $topic_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SpeakingTopic extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'speaking_topics';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'speaking_section_id',
        'topic_name',
    ];

    protected $casts = [
        'topic_name' => 'string',
    ];

    public function speakingSection()
    {
        return $this->belongsTo(SpeakingSection::class, 'speaking_section_id');
    }

    public function questions()
    {
        return $this->hasMany(SpeakingQuestion::class, 'speaking_topic_id');
    }
}
