<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $teacher_plan_id
 * @property string $started_at
 * @property string $expires_at
 * @property string $status
 */
class TeacherSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_subscriptions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'teacher_plan_id',
        'started_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(TeacherPlan::class, 'teacher_plan_id');
    }
}
