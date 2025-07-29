<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string $assignment_id
 * @property string $student_id
 * @property float $score
 * @property int $rank_position
 * @property string $submission_status
 * @property string $submission_date
 */
class ClassLeaderboard extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'class_leaderboards';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'class_id',
        'assignment_id',
        'student_id',
        'score',
        'rank_position',
        'submission_status',
        'submission_date',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'submission_date' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
