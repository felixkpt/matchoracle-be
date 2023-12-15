<?php

namespace App\Services\Validations\Competition;

use App\Models\Competition;
use App\Models\GameSource;
use App\Models\Season;
use App\Rules\ValidGameSourceUri;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Support\Facades\Log;

class CompetitionValidation implements CompetitionValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, Competition::class);

        $validateData = $request->validate(
            [
                'name' => 'required|unique:countries,name,' . $request->id . ',id',
                'slug' => 'nullable|unique:countries,slug,' . $request->id . ',id',
                'abbreviation' => 'nullable|string',
                'code' => 'nullable|string',
                'type' => 'nullable|string',
                'continent_id' => 'required|exists:continents,id',
                'country_id' => 'required|exists:countries,id',
                'last_updated' => 'nullable|date',
                'last_fetch' => 'nullable|date',
                'last_detailed_fetch' => 'nullable|date',
                'emblem' => $this->imageRules(),
                'plan' => 'nullable|string',
                'has_teams' => 'required|integer',
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

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
            'id' => ['required', 'exists:competitions,id'],
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

    function fetchSeasons()
    {

        $validateData = request()->validate(
            [
                'season_id' => 'nullable|exists:seasons,id',
                'matchday' => 'nullable|integer|between:1,200',
            ]
        );

        return $validateData;
    }

    function fetchStandings()
    {

        $validateData = request()->validate(
            [
                'season_id' => 'required|exists:seasons,id',
                'matchday' => 'nullable|integer|between:1,200',
            ]
        );

        return $validateData;
    }

    function fetchMatches($id)
    {

        if (!request()->season_id && $id) {
            $season = Season::where('competition_id', $id)->where('is_current', true)->first();
            if (!$season)
                $season = Season::where('competition_id', $id)->orderby('start_date', 'desc')->first();
            request()->merge(['season_id' => $season->id]);
        }

        $validateData = request()->validate(
            [
                'season_id' => 'required|exists:seasons,id',
                'matchday' => 'nullable|integer|between:1,200',
            ]
        );

        return $validateData;
    }
}
