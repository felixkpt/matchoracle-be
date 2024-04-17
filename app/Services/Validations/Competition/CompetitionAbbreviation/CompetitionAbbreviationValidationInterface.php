<?php

namespace App\Services\Validations\Competition\CompetitionAbbreviation;

use Illuminate\Http\Request;

interface CompetitionAbbreviationValidationInterface
{
    public function store(Request $request): mixed;
}
