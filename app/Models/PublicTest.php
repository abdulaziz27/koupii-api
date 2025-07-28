<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $admin_id
 * @property string $name
 * @property string $type
 * @property string $difficulty
 * @property string $description
 * @property array $question_types
 * @property boolean $is_published
 * @property boolean $is_featured
 * @property int $total_attempts
 * @property float $average_rating
 * @property string $published_at
 */
class PublicTest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'public_tests';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'admin_id',
        'name',
        'type',
        'difficulty',
        'description',
        'question_types',
        'is_published',
        'is_featured',
        'total_attempts',
        'average_rating',
        'published_at',
    ];

    protected $casts = [
        'question_types' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'average_rating' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function attempts()
    {
        return $this->hasMany(PublicTestAttempt::class, 'public_test_id');
    }
}
