<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $vocabulary_id
 * @property string $is_bookmarked
 */
class VocabularyBookmark extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vocabulary_bookmarks';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'vocabulary_id',
        'is_bookmarked',
    ];

    protected $casts = [
        'is_bookmarked' => 'boolean',
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
