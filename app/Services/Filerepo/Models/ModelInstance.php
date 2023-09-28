<?php

namespace App\Services\Filerepo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelInstance extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
}
