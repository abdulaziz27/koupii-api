<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $assignment_id
 * @property string $student_id
 * @property string $status
 * @property float $score
 * @property int $attempt_number
 * @property array $submission_data
 * @property string $started_at
 * @property string $completed_at
 * @property string $submitted_at
 */
class StudentAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_assignments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'status',
        'score',
        'attempt_number',
        'submission_data',
        'started_at',
        'completed_at',
        'submitted_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'submission_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
