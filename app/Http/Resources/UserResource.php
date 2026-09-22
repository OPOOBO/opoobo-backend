<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'opoobo_id' => $this->opoobo_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'initials' => $this->initials,
            'membership_tier' => $this->membership_tier,
            'status' => $this->status,
            'email_verified' => $this->email_verified_at !== null,
            'phone_verified' => $this->phone_verified_at !== null,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'modules' => $this->linkedModules,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
