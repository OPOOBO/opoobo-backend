<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'api_base_url',
        'icon',
        'icon_path',
        'website_url',
        'module_url',
        'test_mode_url',
        'version',
        'permissions',
        'is_active',
        'is_featured',
        'sort_order',
        'category',
        'developer_name',
        'developer_url',
        'developer_id',
        'install_count',
        'check_endpoint',
        'create_endpoint',
        'change_password_endpoint',
        'sso_endpoint',
        'required_fields',
        'screenshots',
        'required_bridge_apis',
        'ssl_valid',
        'url_loads',
        'bridge_detected',
        'last_preflight_status',
        'last_preflight_at',
        'review_status',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
        'admin_tested',
        'admin_tested_at',
    ];

    protected function casts(): array
    {
        return [
            'required_fields' => 'array',
            'permissions' => 'array',
            'screenshots' => 'array',
            'required_bridge_apis' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'install_count' => 'integer',
            'developer_id' => 'integer',
            'ssl_valid' => 'boolean',
            'url_loads' => 'boolean',
            'bridge_detected' => 'boolean',
            'admin_tested' => 'boolean',
            'last_preflight_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'admin_tested_at' => 'datetime',
        ];
    }

    public function moduleUsers()
    {
        return $this->hasMany(ModuleUser::class, 'module_name', 'name');
    }

    public function developer()
    {
        return $this->belongsTo(Developer::class);
    }

    public function reviews()
    {
        return $this->hasMany(MiniAppReview::class);
    }
}
