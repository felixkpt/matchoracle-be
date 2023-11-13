<?php

namespace App\Services\Validations\PostStatus;

use Illuminate\Http\Request;

interface PostStatusValidationInterface
{
    public function store(Request $request): mixed;    
}
