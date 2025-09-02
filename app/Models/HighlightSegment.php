<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $breakdown_id
 * @property int|null $start_char_index
 * @property int|null $end_char_index
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class HighlightSegment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'highlight_segments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'breakdown_id',
        'passage_type',
        'start_char_index',
        'end_char_index',
    ];

    protected $casts = [
        'passage_type' => 'string',
    ];

    public function breakdown()
    {
        return $this->belongsTo(QuestionBreakdown::class, 'breakdown_id');
    }
}
