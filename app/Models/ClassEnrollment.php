<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string $student_id
 * @property string $status
 * @property string $enrolled_at
 */
class ClassEnrollment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'class_enrollments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'class_id',
        'student_id',
        'status',
        'enrolled_at',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
