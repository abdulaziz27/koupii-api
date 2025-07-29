<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property float $price
 * @property int $max_students
 * @property int $max_classrooms
 * @property boolean $can_create_tests
 * @property boolean $priority_support
 * @property array $features
 * @property boolean $is_active
 */
class TeacherPlan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_plans';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'price',
        'max_students',
        'max_classrooms',
        'can_create_tests',
        'priority_support',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'can_create_tests' => 'boolean',
        'priority_support' => 'boolean',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(TeacherSubscription::class, 'teacher_plan_id');
    }
}
