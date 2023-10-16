<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TopLevelDomainRule implements Rule
{
    public function passes($attribute, $value)
    {
        // Use parse_url to get the path and query components
        $urlComponents = parse_url($value);

        // Check if the path and query components are empty
        return empty($urlComponents['path']) && empty($urlComponents['query']);
    }

    public function message()
    {
        return 'The :attribute must be a top-level domain (TLD) without path or query parameters.';
    }
}
