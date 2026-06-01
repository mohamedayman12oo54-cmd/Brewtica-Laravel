<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class UserPhone extends Model
{
    /** @use HasFactory<\Database\Factories\UserPhoneFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'is_primary',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    // ====== Relationships ======

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
