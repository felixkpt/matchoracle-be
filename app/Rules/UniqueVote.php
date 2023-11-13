<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\GameVote;


class UniqueVote implements Rule
{
    protected $gameId;

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }

    public function passes($attribute, $value)
    {
        $user_id = auth()->id() ?? 0;
        $ip = request()->ip();

        // Check if a vote from the same user or IP address for the same game already exists
        return !GameVote::where('game_id', $this->gameId)
            ->where(function ($query) use ($user_id, $ip) {
                $query->where('user_id', $user_id)->orWhere('user_ip', $ip);
            })
            ->exists();
    }

    public function message()
    {
        return 'You have already voted for this game.';
    }
}
