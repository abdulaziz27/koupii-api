<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $passage_number
 * @property string $title
 * @property string $content
 * @property string $audio_file_path
 * @property array $metadata
 */
class TestPassage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_passages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'passage_number',
        'title',
        'content',
        'audio_file_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'passage_id');
    }
}
