<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string|null $test_id
 * @property string $title
 * @property string|null $description
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $close_date
 * @property bool $is_published
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'due_date' => 'datetime',
        'close_date' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function studentAssignments()
    {
        return $this->hasMany(StudentAssignment::class, 'assignment_id');
    }
}
