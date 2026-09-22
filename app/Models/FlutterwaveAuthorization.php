<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlutterwaveAuthorization extends Model
{
    protected $fillable = [
        'user_id',
        'authorization_code',
        'card_type',
        'last_four',
        'exp_month',
        'exp_year',
        'bank_name',
        'is_reusable',
    ];

    protected function casts(): array
    {
        return [
            'is_reusable' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
