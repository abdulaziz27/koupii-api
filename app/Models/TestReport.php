<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $class_id
 * @property int $total_submissions
 * @property float|null $average_score
 * @property float|null $highest_score
 * @property float|null $lowest_score
 * @property string|null $most_mistaken_question_id
 * @property \Carbon\Carbon|null $report_generated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TestReport extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_reports';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'class_id',
        'total_submissions',
        'average_score',
        'highest_score',
        'lowest_score',
        'most_mistaken_question_id',
        'report_generated_at',
    ];

    protected $casts = [
        'average_score' => 'float',
        'highest_score' => 'float',
        'lowest_score' => 'float',
        'report_generated_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function mostMistakenQuestion()
    {
        return $this->belongsTo(TestQuestion::class, 'most_mistaken_question_id');
    }

    public function leaderboardEntries()
    {
        return $this->hasMany(LeaderboardEntry::class, 'test_report_id');
    }
}
