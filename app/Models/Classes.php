<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $teacher_id
 * @property string $name
 * @property string $description
 * @property string $class_code
 * @property string $cover_image
 * @property string $is_active
 */
class Classes extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'class_code',
        'cover_image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function invitations()
    {
        return $this->hasMany(ClassInvitation::class, 'class_id');
    }

    public function vocabularies()
    {
        return $this->belongsToMany(Vocabulary::class, 'class_vocabularies', 'class_id', 'vocabulary_id')->withPivot('assigned_at')->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function analytics()
    {
        return $this->hasMany(ClassAnalytic::class, 'class_id');
    }

    public function testReports()
    {
        return $this->hasMany(TestReport::class, 'class_id');
    }
}
