<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $name
 * @property string $type
 * @property string $difficulty
 * @property string $description
 * @property string $time_limit_minutes
 * @property string $total_questions
 * @property string $is_published
 * @property string $is_public
 * @property string $allow_repetition
 * @property string $max_repetition_count
 * @property array $settings
 */
class Test extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tests';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'creator_id',
        'name',
        'type',
        'difficulty',
        'description',
        'time_limit_minutes',
        'total_questions',
        'is_published',
        'is_public',
        'allow_repetition',
        'max_repetition_count',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_published' => 'boolean',
        'is_public' => 'boolean',
        'allow_repetition' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'test_id');
    }

    public function passages()
    {
        return $this->hasMany(TestPassage::class, 'test_id');
    }

    public function contentFiles()
    {
        return $this->hasMany(TestContentFile::class, 'test_id');
    }

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'test_id');
    }

    public function sessions()
    {
        return $this->hasMany(TestSession::class, 'test_id');
    }

    public function questionInstructions()
    {
        return $this->hasMany(QuestionInstruction::class);
    }
}
