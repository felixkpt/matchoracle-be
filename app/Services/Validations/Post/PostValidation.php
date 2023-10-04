<?php

namespace App\Services\Validations\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostValidation implements PostValidationInterface
{

    public function store(Request $request): mixed
    {

        $rules = [
            'category_id' => 'required|exists:post_categories,id',
            'topic_id' => 'nullable|exists:post_topics,id',
            'title' => 'required|string|max:255|unique:posts,title,' . $request->id . ',id', // Ensure title is unique
            'content_short' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => ['required', 'image'],
            'priority_number' => 'nullable|integer|between:0,99999999',
            'status_id' => 'required|exists:post_statuses,id',
        ];

        if ($request->id) {
            $rules['image'] = ['nullable'];
        }


        // Validate the incoming request data
        $validatedData = $request->validate($rules);
        unset($validatedData['image']);

        if ($request->slug) {
            $slug = Str::slug($validatedData['slug']);
        } else {
            // Generate the slug from the title
            $slug = Str::slug($validatedData['title']);

            if (!$request->id) {

                // Check if the generated slug is unique, if not, add a suffix
                $count = 1;
                while (Post::where('slug', $slug)->exists()) {
                    $slug = Str::slug($slug) . '-' . Str::random($count);
                    $count++;
                }
            }
        }

        // Include the generated slug in the validated data
        $validatedData['slug'] = Str::lower($slug);
        if (!$request->id) {
            $validatedData['user_id'] = auth()->user()->id;
        }
        return $validatedData;
    }
}
