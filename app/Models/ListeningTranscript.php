<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $passage_id
 * @property string|null $title
 * @property string|null $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ListeningTranscript extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'listening_transcripts';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'passage_id',
        'title',
        'content',
    ];

    public function passage()
    {
        return $this->belongsTo(ListeningPassage::class, 'passage_id');
    }
}
