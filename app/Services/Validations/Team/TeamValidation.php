<?php

namespace App\Services\Validations\Team;

use App\Models\Team;
use App\Rules\ValidGameSourceUri;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Support\Facades\Log;

class TeamValidation implements TeamValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, Team::class);

        $validateData = $request->validate(
            [
                'name' => 'required|unique:countries,name,' . $request->id . ',id',
                'slug' => 'nullable|unique:countries,slug,' . $request->id . ',id',
                'abbreviation' => 'nullable|string',
                'continent_id' => 'required|exists:continents,id',
                'country_id' => 'required|exists:countries,id',
                'last_fetch' => 'nullable|date',
                'last_detailed_fetch' => 'nullable|date',
                'creat' => $this->imageRules(),
                'has_teams' => 'required|integer',
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        return $validateData;
    }

    public function storeFetch(): mixed
    {
        request()->validate([
            'source' => 'required:url'
        ]);
    }

    function addSources()
    {

        request()->validate([
            'id' => ['required', 'exists:teams,id'],
        ]);

        $data = request()->except('id');

        $validateData = [];
        // Validate each ID and URI using the custom rule
        foreach ($data as $key => $value) {

            request()->validate([
                $key => ['nullable', new ValidGameSourceUri],
            ]);

            $m = preg_match('/^([^_]+)_uri$/', $key, $matches);
            if ($m) {
                $validateData[$matches[1]] = ['uri' => $value, 'source_id' => $data[$matches[1] . '_source_id'], 'subscription_expires' => isset($data[$matches[1] . '_subscription_expires_check_input']) ? 'never' : $data[$matches[1] . '_subscription_expires']];
            }
        }

        return $validateData;
    }
}
