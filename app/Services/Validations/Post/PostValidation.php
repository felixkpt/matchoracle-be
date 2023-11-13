<?php

namespace App\Services\Validations\Post;

use App\Models\Post;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Http\Request;

class PostValidation implements PostValidationInterface
{

    use ValidationFormatter;

    public function store(Request $request): mixed
    {
        $this->ensuresSlugIsUnique($request->title, Post::class);

        $rules = [
            'category_id' => 'required|exists:post_categories,id',
            'topic_id' => 'nullable|exists:post_topics,id',
            'title' => 'required|string|max:255|unique:posts,title,' . $request->id . ',id', // Ensure title is unique
            'slug' => 'nullable|unique:posts,slug,' . $request->id . ',id',
            'content_short' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => ['nullable', 'image'],
            'priority_number' => 'nullable|integer|between:1,99999999',
            'status_id' => 'required|exists:post_statuses,id',
        ];

        if ($request->id) {
            $rules['image'] = ['nullable'];
        }

        // Validate the incoming request data
        $validatedData = $request->validate($rules);
        unset($validatedData['image']);

        return $validatedData;
    }
}
