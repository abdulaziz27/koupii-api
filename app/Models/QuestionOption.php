<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $question_id
 * @property string|null $option_key
 * @property string|null $option_text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
    ];

    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}
