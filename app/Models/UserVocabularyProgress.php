<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $vocabulary_id
 * @property string $mastery_level
 * @property string $review_count
 * @property string $last_reviewed_at
 */
class UserVocabularyProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_vocabulary_progress';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'vocabulary_id',
        'mastery_level',
        'review_count',
        'last_reviewed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vocabulary()
    {
        return $this->belongsTo(Vocabulary::class);
    }
}
