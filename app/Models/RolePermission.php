<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'module',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }
}
