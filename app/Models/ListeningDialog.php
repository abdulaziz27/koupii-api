<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $passage_id
 * @property string|null $conversation_title
 * @property string|null $speaker_name
 * @property string|null $speech_content
 * @property int|null $sequence_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ListeningDialog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'listening_dialogs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'passage_id',
        'conversation_title',
        'speaker_name',
        'speech_content',
        'sequence_number',
    ];

    public function passage()
    {
        return $this->belongsTo(ListeningPassage::class, 'passage_id');
    }
}
