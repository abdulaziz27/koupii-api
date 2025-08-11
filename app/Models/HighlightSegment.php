<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $breakdown_id
 * @property string $passage_type
 * @property int|null $start_char_index
 * @property int|null $end_char_index
 * @property float|null $start_time_seconds
 * @property float|null $end_time_seconds
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
        'start_time_seconds',
        'end_time_seconds',
    ];

    protected $casts = [
        'passage_type' => 'string',
        'start_time_seconds' => 'float',
        'end_time_seconds' => 'float',
    ];

    public function breakdown()
    {
        return $this->belongsTo(QuestionBreakdown::class, 'breakdown_id');
    }
}
