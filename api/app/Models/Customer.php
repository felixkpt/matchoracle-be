<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class Customer extends Model
{
    use HasFactory;
    // use Searchable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'alternate_number',
        'email',
        'status',
    ];

    public static function getTotalCount()
    {
        return self::count();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function demo()
    {
        return $this->hasMany(Demo::class);
    }
}
