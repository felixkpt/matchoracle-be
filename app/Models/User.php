<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        ExcludeSystemFillable, CommonModelRelationShips;

    // Use an alias for the permissions relationship
    use HasPermissions {
        HasPermissions::permissions as direct_permissions;
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'phone',
        'status_id',
        'two_factor_valid',
        'last_login_date',
        'two_factor_expires_at',
        'two_factor_code',
        'email_verified_at',
        'password',
        'api_token',
        'avatar',
        'session_id',
        'is_session_valid',
        'allowed_session_no',
        'is_online',
        'remember_token',
        'two_factor_enabled',
        'theme',
        'default_role_id',
    ];

    protected $systemFillable = [
        'status_id',
        'first_name',
        'middle_name',
        'last_name',
        'two_factor_valid',
        'last_login_date',
        'two_factor_expires_at',
        'two_factor_code',
        'email_verified_at',
        'api_token',
        'session_id',
        'is_session_valid',
        'is_online',
        'remember_token',
        'theme',
        'default_role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'first_name',
        'middle_name',
        'last_name',
        'two_factor_valid',
        'last_login_date',
        'two_factor_expires_at',
        'two_factor_code',
        'email_verified_at',
        'session_id',
        'is_session_valid',
        'is_online',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    function votes()
    {
        return $this->hasMany(GameVote::class);
    }
    
}
