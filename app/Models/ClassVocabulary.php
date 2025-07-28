<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string $vocabulary_id
 * @property string $assigned_at
 */
class ClassVocabulary extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'class_vocabulary';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'class_id',
        'vocabulary_id',
        'assigned_at',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function vocabulary()
    {
        return $this->belongsTo(Vocabulary::class);
    }
}
