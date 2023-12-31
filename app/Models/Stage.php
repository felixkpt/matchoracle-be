<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = ['name']; // Add other fillable attributes if needed

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
