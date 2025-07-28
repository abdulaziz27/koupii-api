<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $category
 * @property array $template_structure
 * @property array $scoring_rules
 * @property boolean $is_active
 */
class QuestionType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_types';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
        'template_structure',
        'scoring_rules',
        'is_active',
    ];

    protected $casts = [
        'template_structure' => 'array',
        'scoring_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'question_type_id');
    }

    public function questionInstructions()
    {
        return $this->hasMany(QuestionInstruction::class);
    }
}
