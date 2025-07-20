<?php

namespace App\Services\Validations\Competition\CompetitionAbbreviation;

use App\Models\Competition;
use App\Services\Validations\CommonValidations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetitionAbbreviationValidation implements CompetitionAbbreviationValidationInterface
{
    use CommonValidations;

    public function store(Request $request): mixed
    {
        $validateData = request()->validate([
            'competition_id' => 'required|exists:competitions,id',
        ]);

        request()->merge(['country_id' => Competition::find($validateData['competition_id'])->country_id ?? 0]);

        $validateData = request()->validate([
            'competition_id' => 'required|exists:competitions,id',
            'name' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    // Check if the combination of name and country_id already exists
                    $exists = DB::table('competition_abbreviations')
                        ->where('name', $value)
                        ->where('country_id', $request->country_id)
                        ->where('id', '!=', $request->id) // Exclude the current record if editing
                        ->exists();

                    if ($exists) {
                        $fail('The combination of name and country must be unique.');
                    }
                },
            ],
            'country_id' => 'required|exists:countries,id',
            'is_international' => 'nullable',
        ]);


        $validateData['is_international'] = $validateData['is_international'] ?? 0;

        return $validateData;
    }
}
