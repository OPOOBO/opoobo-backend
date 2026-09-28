<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'model',
        'api_key',
        'is_enabled',
        'sort_order',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function maskedKey(): string
    {
        $key = (string) $this->api_key;
        if ($key === '') {
            return '';
        }

        return '••••'.substr($key, -4);
    }
}
