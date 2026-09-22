<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniAppReview extends Model
{
    protected $fillable = [
        'module_id',
        'user_id',
        'rating',
        'review',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
