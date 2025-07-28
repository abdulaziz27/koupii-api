<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $question_id
 * @property string $option_key
 * @property string $option_text
 * @property bool $is_correct
 * @property int $display_order
 */
class QuestionOption extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_options';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_id',
        'option_key',
        'option_text',
        'is_correct',
        'display_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}
