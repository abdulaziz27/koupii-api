<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $color_code
 */
class VocabularyCategory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vocabulary_categories';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'color_code',
    ];

    public function vocabularies()
    {
        return $this->hasMany(Vocabulary::class, 'category_id');
    }
}
