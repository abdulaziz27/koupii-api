<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $section_type
 * @property string|null $description
 * @property int|null $prep_time_seconds
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SpeakingSection extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'speaking_sections';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'section_type',
        'description',
        'prep_time_seconds',
    ];

    protected $casts = [
        'section_type' => 'string',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function topics()
    {
        return $this->hasMany(SpeakingTopic::class, 'speaking_section_id');
    }
}
