<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $class_id
 * @property string $assignment_id
 * @property float $average_score
 * @property array $skill_breakdown
 * @property array $performance_insights
 * @property array $common_mistakes
 * @property string $calculated_at
 */
class ClassAnalytic extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'class_analytics';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'class_id',
        'assignment_id',
        'average_score',
        'skill_breakdown',
        'performance_insights',
        'common_mistakes',
        'calculated_at',
    ];

    protected $casts = [
        'average_score' => 'decimal:2',
        'skill_breakdown' => 'array',
        'performance_insights' => 'array',
        'common_mistakes' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }
}
