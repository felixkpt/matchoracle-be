<?php

namespace App\Services\Validations\Post\Category;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostCategoryValidation implements PostCategoryValidationInterface
{

    public function store(Request $request): mixed
    {

        // Validate the incoming request data
        $validatedData = $request->validate([
            'parent_category_id' => 'nullable|exists:post_categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('post_categories', 'name')->where(function ($query) use ($request) {
                    return $query->where('parent_category_id', $request->parent_category_id);
                })->ignore($request->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('post_categories', 'slug')->ignore($request->id),
            ],
            'description' => 'nullable|string|max:255',
            'image' => 'required|image',
            'priority_number' => 'nullable|integer|between:1,99999999',
        ]);

        return $validatedData;
    }
}
