<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionJob extends Model
{
    use HasFactory;

    public $timestamps = false; // no Eloquent managed created_at/updated_at

    protected $fillable = ['process_id', 'status', 'attempts', 'reserved_at', 'available_at', 'created_at'];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_at', '<=', time())
            ->whereNull('reserved_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    // convenience: cast these to Carbon automatically
    protected $casts = [
        'reserved_at' => 'datetime:U',
        'available_at' => 'datetime:U',
        'created_at'  => 'datetime:U',
    ];
}
