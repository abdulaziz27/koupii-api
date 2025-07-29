<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $email
 * @property string $token
 * @property string $created_at
 */
class PasswordResetToken extends Model
{
    use HasFactory;

    protected $table = 'password_reset_tokens';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'email';
    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
