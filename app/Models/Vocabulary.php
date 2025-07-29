<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $teacher_id
 * @property string $category_id
 * @property string $word
 * @property string $translation
 * @property string $spelling
 * @property string $explanation
 * @property string $audio_file_path
 * @property string $is_public
 */
class Vocabulary extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vocabularies';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'teacher_id',
        'category_id',
        'word',
        'translation',
        'spelling',
        'explanation',
        'audio_file_path',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(VocabularyCategory::class, 'category_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_vocabularies', 'vocabulary_id', 'class_id')->withPivot('assigned_at')->withTimestamps();
    }

    public function bookmarks()
    {
        return $this->hasMany(UserVocabularyBookmark::class, 'vocabulary_id');
    }

    public function progresses()
    {
        return $this->hasMany(UserVocabularyProgress::class, 'vocabulary_id');
    }
}
