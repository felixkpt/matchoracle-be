<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, ExcludeSystemFillable, HasUlids;
    protected $fillable = ['name', 'guard_name', 'parent_folder', 'uri', 'title', 'user_id', 'slug', 'icon', 'hidden'];
    protected $systemFillable = ['parent_folder', 'uri', 'title', 'slug', 'icon', 'hidden'];
    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = ['parent_folder', 'uri', 'title', 'slug', 'icon', 'hidden'];
}
