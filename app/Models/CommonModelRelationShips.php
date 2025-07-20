<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait CommonModelRelationShips
{
    function user()
    {
        return $this->belongsTo(User::class);
    }

    function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function image(): Attribute
    {
        return $this->resolvePath();
    }

    /**
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function flag(): Attribute
    {
        return $this->resolvePath();
    }
    /**
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function logo(): Attribute
    {
        return $this->resolvePath();
    }

    function resolvePath()
    {
        return Attribute::make(
            get: function ($value) {

                if ($value) {
                    if (env('FILESYSTEM_DRIVER', 'local') == 'local') {
                        return env('APP_URL') . Storage::url($value);
                    } else {
                        $path = Str::startsWith($value, config('app.gcs_project_folder')) ? $value : config('app.gcs_project_folder') . '/' . $value;
                        return Storage::url($path);
                    }
                }
                return null;
            },
            set: fn($value) => strtolower($value),
        );
    }

    public static function boot()
    {
        parent::boot();
        static::creating(fn($model) => defaultColumns($model));
    }
}
