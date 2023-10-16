<?php

namespace App\Services\Validations\Post\Category;

use Illuminate\Http\Request;

interface PostCategoryValidationInterface
{
    public function store(Request $request): mixed;
    
}
