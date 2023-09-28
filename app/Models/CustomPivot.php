<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class CustomPivot extends MorphPivot
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $casts = [
        'model_id' => 'string',
        'role_id' => 'string',
    ];
}
