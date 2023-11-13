<?php

namespace App\Services\Validations\Post;

use Illuminate\Http\Request;

interface PostValidationInterface
{
    public function store(Request $request): mixed;
    
}
