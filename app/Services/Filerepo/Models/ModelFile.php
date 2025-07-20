<?php

namespace App\Services\Filerepo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ModelFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_instance_id',
        'model_id',
        'merged_from_id',
        'name',
        'unique_name',
        'extension',
        'disk',
        'path',
        'size',
        'update_id',
        'created_by',
        'status',
        'form_token',
    ]; 
       
    public function attachable()
    {
        return $this->morphTo();
    }

     /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_path', 'type'];

        /**
     * Get the custom attribute based on multiple columns.
     *
     * @return mixed
     */
    public function getFullPathAttribute()
    {
        // Define your logic here to generate the custom attribute based on multiple columns
        $path = Storage::disk($this->disk)->url($this->path);
        
        return $path;
    }
        /**
     * Get the custom attribute based on multiple columns.
     *
     * @return mixed
     */
    public function getTypeAttribute()
    {
        // Define your logic here to generate the custom attribute based on multiple columns
        $type = $this->extension;
        
        return $type;
    }
}
