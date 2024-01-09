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
                $query->where(fn ($q) => $q->where('user_id', $user_id)->orWhere('user_ip', $ip))
                    ->when(request('type') == 'winner', fn ($q) => $q->whereNotNull('winner'))
                    ->when(request('type') == 'over_under', fn ($q) => $q->whereNotNull('over_under'))
                    ->when(request('type') == 'bts', fn ($q) => $q->whereNotNull('bts'));
            })
            ->exists();
    }

    public function message()
    {
        return 'You have already voted for this game.';
    }
}
