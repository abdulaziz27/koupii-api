<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property string $color_code
 */
class VocabularyCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_code',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function vocabularies()
    {
        return $this->hasMany(Vocabulary::class, 'category_id');
    }
}
