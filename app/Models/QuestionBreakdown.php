<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $question_id
 * @property string|null $explanation
 * @property bool $has_highlight
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class QuestionBreakdown extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_breakdowns';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_id',
        'explanation',
        'has_highlight',
    ];

    protected $casts = [
        'has_highlight' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }

    public function highlightSegments()
    {
        return $this->hasMany(HighlightSegment::class, 'breakdown_id');
    }
}
