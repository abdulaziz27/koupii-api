<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $teacher_id
 * @property int $total_students
 * @property int $total_classes
 * @property int $total_tests_created
 * @property int $total_assignments
 * @property array $monthly_activity
 * @property array $class_performance_summary
 * @property string $last_calculated
 */
class TeacherDashboardStat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_dashboard_stats';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'teacher_id',
        'total_students',
        'total_classes',
        'total_tests_created',
        'total_assignments',
        'monthly_activity',
        'class_performance_summary',
        'last_calculated',
    ];

    protected $casts = [
        'monthly_activity' => 'array',
        'class_performance_summary' => 'array',
        'last_calculated' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
