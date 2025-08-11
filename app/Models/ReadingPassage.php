<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string|null $title
 * @property string|null $description
 * @property int|null $duration_minutes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReadingPassage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'reading_passages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'title',
        'description',
        'duration_minutes',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function questionGroups()
    {
        return $this->hasMany(ReadingQuestionGroup::class, 'passage_id');
    }
}
