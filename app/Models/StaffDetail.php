<?php

namespace App\Models;

use App\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDetail extends Model
{
    /** @use HasFactory<\Database\Factories\StaffDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title',
        'salary',
        'hire_date',
        'shift',
        'department',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary'    => 'decimal:2',
            'shift' => Shift::class,
        ];
    }

    // ======= Relationships =======

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
