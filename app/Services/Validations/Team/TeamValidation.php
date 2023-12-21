<?php

namespace App\Services\Validations\Team;

use App\Models\Country;
use App\Models\GameSource;
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
                'name' => 'required|string',
                'slug' => 'nullable|string|unique:teams,slug,' . $request->id . ',id',
                'short_name' => 'nullable|string',
                'tla' => 'nullable|string',
                'logo' => $this->imageRules(),
                'address_id' => 'nullable|exists:addresses,id',
                'website' => 'nullable|url',
                'founded' => 'nullable|integer',
                'club_colors' => 'nullable|string',
                'venue_id' => 'nullable|exists:venues,id',
                'competition_id' => 'exists:competitions,id',
                'country_id' => 'required|exists:countries,id',
                'last_fetch' => 'nullable|date',
                'last_detailed_fetch' => 'nullable|date',
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        $validateData['continent_id'] = Country::find($request->input('country_id'))->continent_id ?? 0;

        return $validateData;
    }

    public function storeFromSource()
    {
        $data = request()->except('id');

        $validateData = [];
        // Validate each ID and URI using the custom rule
        foreach ($data as $key => $value) {

            $m = preg_match('/^([^_]+)_uri$/', $key, $matches);

            if (!isset($matches[1]) || !GameSource::find($matches[1])) continue;

            request()->validate([
                $key => ['nullable', new ValidGameSourceUri],
            ]);

            if ($m) {
                $validateData[$matches[1]] = ['uri' => $value, 'source_id' => $data[$matches[1] . '_source_id'], 'subscription_expires' => isset($data[$matches[1] . '_subscription_expires_check_input']) ? 'never' : $data[$matches[1] . '_subscription_expires']];
            }
        }

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

    function updateCoach()
    {
        $validateData = request()->validate([
            'coach_id' => ['required', 'exists:coaches,id'],
            'start' => ['required', 'date'],
            'until' => ['required', 'date'],
        ]);

        return $validateData;
    }
}
