<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string $provider_token
 */
class SocialAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'social_accounts';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
