<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $test_id
 * @property string $filename
 * @property string $file_path
 * @property string $file_type
 * @property string $mime_type
 * @property string $file_size
 * @property array $ocr_data
 */
class TestContentFile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test_content_files';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_id',
        'filename',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'ocr_data',
    ];

    protected $casts = [
        'ocr_data' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
