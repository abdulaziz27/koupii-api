<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_report_id
 * @property string $student_id
 * @property float|null $score
 * @property string $status
 * @property \Carbon\Carbon|null $submission_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class LeaderboardEntry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'leaderboard_entries';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_report_id',
        'student_id',
        'score',
        'status',
        'submission_date',
    ];

    protected $casts = [
        'score' => 'float',
        'status' => 'string',
        'submission_date' => 'datetime',
    ];

    public function testReport()
    {
        return $this->belongsTo(TestReport::class, 'test_report_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
