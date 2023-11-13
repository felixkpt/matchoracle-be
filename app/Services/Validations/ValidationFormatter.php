<?php

namespace App\Services\Validations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait ValidationFormatter
{

    function ensuresSlugIsUnique($name, $model)
    {
        $slug = '';

        if (!Schema::hasColumn((new $model())->getTable(), 'slug')) return false;

        if (request()->slug) {
            $slug = Str::slug(request()->slug);
        } else {
            // Generate the slug from the title
            $slug = Str::slug($name);

            if (!request()->id) {

                // Check if the generated slug is unique, if not, add a suffix
                $count = 1;
                while ($model::where('slug', $slug)->exists()) {
                    $slug = Str::slug($slug) . '-' . Str::random($count);
                    $count++;
                }
            }
        }

        request()->merge(['slug' => strlen($slug) > 0 ? $slug : null]);
    }
}
