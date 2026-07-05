<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
     use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'password',
        'profile_picture_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profilePicture()
    {
        return $this->belongsTo(ProfilePicture::class, 'profile_picture_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role', 'role');
    }

    public function canModule(string $module, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $permission = RolePermission::where('role', $this->role)
            ->where('module', $module)
            ->first();

        if (!$permission) {
            return false;
        }

        return match ($action) {
            'view' => $permission->can_view,
            'create' => $permission->can_create,
            'update' => $permission->can_update,
            'delete' => $permission->can_delete,
            default => false,
        };
    }

    public function getPermissions(): array
    {
        $permissions = RolePermission::where('role', $this->role)->get();

        return $permissions->mapWithKeys(function ($perm) {
            return [$perm->module => [
                'view' => $perm->can_view,
                'create' => $perm->can_create,
                'update' => $perm->can_update,
                'delete' => $perm->can_delete,
            ]];
        })->toArray();
    }
}
