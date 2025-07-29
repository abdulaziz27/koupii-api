<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $session_id
 * @property string $user_id
 * @property string $test_id
 * @property string $test_type
 * @property float $total_score
 * @property float $percentage_score
 * @property int $questions_correct
 * @property int $questions_incorrect
 * @property int $questions_missed
 * @property array $detailed_breakdown
 * @property array $performance_analytics
 * @property string $calculated_at
 */
class TestResult extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_results';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'user_id',
        'test_id',
        'test_type',
        'total_score',
        'percentage_score',
        'questions_correct',
        'questions_incorrect',
        'questions_missed',
        'detailed_breakdown',
        'performance_analytics',
        'calculated_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'percentage_score' => 'decimal:2',
        'detailed_breakdown' => 'array',
        'performance_analytics' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
