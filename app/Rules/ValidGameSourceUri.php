<?php

namespace App\Rules;

use App\Services\Client;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidGameSourceUri implements Rule
{
    protected $errorMessage;

    public function passes($attribute, $value)
    {
        // Extract the ID from the attribute name (e.g., '01hcc3cghskkpas7zmtsz9yds7_uri')
        if (preg_match('/^([^_]+)_uri$/', $attribute, $matches)) {
            $id = $matches[1];

            // Check if the ID exists in the games_sources table
            $exists = DB::table('game_sources')->where('id', $id)->first();

            return true;
            // If the ID exists, validate the URI
            if ($exists) {

                $existsUrl = rtrim($exists->url, '/') . '/';

                // Concatenate the existing URL and $value with a single slash
                $url = $existsUrl . ltrim($value, '/');

                if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                    return true;
                    $status = Client::requestStatus($url);

                    if ($status === 200) {
                        return true;
                    } else {
                        $this->errorMessage = "The concatenated URL must have a status of 200. (got $status)";
                    }
                } else {
                    $this->errorMessage = "The :attribute must be a valid URI for the corresponding game source ID.";
                }
            } else {
                $this->errorMessage = "The corresponding game source ID does not exist.";
            }
        } elseif (preg_match('/^([^_]+)_source_id$|^([^_]+)_subscription_expires$|^([^_]+)_subscription_expires_check_input$/', $attribute, $matches)) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return $this->errorMessage ?? 'Validation failed for :attribute.';
    }
}
