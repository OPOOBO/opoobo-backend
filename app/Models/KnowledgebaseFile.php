<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgebaseFile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'mime',
        'size',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
