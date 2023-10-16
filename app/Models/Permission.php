<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, HasUlids, CommonModelRelationShips, ExcludeSystemFillable;
    protected $keyType = 'string';

    protected $fillable = ['name', 'guard_name', 'parent_folder', 'uri', 'title', 'user_id', 'slug', 'icon', 'hidden', 'status_id'];
    protected $systemFillable = ['parent_folder', 'uri', 'title', 'slug', 'icon', 'hidden'];

}
