<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string $test_id
 * @property string $title
 * @property string $description
 * @property string $due_date
 * @property string $close_date
 * @property string $is_published
 * @property string $auto_grade
 * @property string $allow_retake
 * @property string $max_attempts
 */
class Assignment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assignments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'class_id',
        'test_id',
        'title',
        'description',
        'due_date',
        'close_date',
        'is_published',
        'auto_grade',
        'allow_retake',
        'max_attempts',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'auto_grade' => 'boolean',
        'allow_retake' => 'boolean',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function classAnalytics()
    {
        return $this->hasMany(ClassAnalytic::class, 'assignment_id');
    }

    public function studentAssignments()
    {
        return $this->hasMany(StudentAssignment::class, 'assignment_id');
    }

    public function classLeaderboards()
    {
        return $this->hasMany(ClassLeaderboard::class, 'assignment_id');
    }
}
