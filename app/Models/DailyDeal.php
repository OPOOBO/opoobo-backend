<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyDeal extends Model
{
    protected $fillable = [
        'label',
        'subtitle',
        'tag',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
