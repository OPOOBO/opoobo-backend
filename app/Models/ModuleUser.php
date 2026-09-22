<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleUser extends Model
{
    protected $fillable = [
        'user_id',
        'module_name',
        'module_uid',
        'module_email',
        'module_token',
        'is_active',
        'linked_at',
    ];

    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_name', 'name');
    }
}
