<?php

namespace App\Services\Validations\BettingTips;

use Illuminate\Http\Request;

interface BettingTipsValidationInterface
{
    public function subscribe(Request $request): mixed;
    
}
