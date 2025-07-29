<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $public_test_id
 * @property float $score
 * @property int $time_spent_seconds
 * @property array $performance_data
 * @property float $rating
 * @property string $feedback
 * @property string $completed_at
 */
class PublicTestAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'public_test_attempts';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'public_test_id',
        'score',
        'time_spent_seconds',
        'performance_data',
        'rating',
        'feedback',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'performance_data' => 'array',
        'rating' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function publicTest()
    {
        return $this->belongsTo(PublicTest::class, 'public_test_id');
    }
}
