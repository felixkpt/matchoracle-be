<?php

namespace App\Http\Controllers\SourcesTest;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SourcesTestController extends Controller
{
    protected GameSourceStrategy $sourceContext;

    public function __construct(protected Competition $model)
    {
        $this->sourceContext = new GameSourceStrategy();
        
        ini_set('max_execution_time', 60 * 30);
        request()->merge(['without_response' => true]);

    }

    public function index(Request $request)
    {
        $competitions = $this->model->orderby('country_id')->get();

        return view('sources-test/index', ['competitions' => $competitions]);
    }

    protected function setSourceStrategy(string $source): void
    {
        switch ($source) {
            case 'forebet':
                $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
                break;
            case 'soccerway':
                $this->sourceContext->setGameSourceStrategy(new SoccerwayStrategy());
                break;
            case 'footballdata':
                $this->sourceContext->setGameSourceStrategy(new FootballdataStrategy());
                break;
            default:
                throw new \InvalidArgumentException("Invalid source: $source");
        }
    }

    public function run(Request $request)
    {
        $source = $request->get('source', 'forebet');
        $this->setSourceStrategy($source);

        $job = $request->get('job');

        if (empty($job)) {
            return redirect()->back()->with('error', 'No job selected.');
        }

        return match ($job) {
            'match' => $this->fetchMatch($request),
            'matches' => $this->fetchMatches($request),
            'seasons' => $this->fetchSeasons($request),
            'standings' => $this->fetchStandings($request),
            default => redirect()->back()->with('error', 'Invalid job selected.'),
        };
    }

    public function fetchSeasons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $competition_id = $request->competition_id;
        $seasonsHandler = $this->sourceContext->seasonsHandler();
        $results = $seasonsHandler->fetchSeasons($competition_id);

        return redirect()->back()->with('success', 'Seasons fetched successfully!')->with('seasons', $results);
    }

    public function fetchStandings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $competition_id = $request->competition_id;
        $season_id = $request->season_id ?? Season::where('competition_id', $competition_id)
            ->orderBy('start_date', 'desc')
            ->first()
            ?->id;

        $standingsHandler = $this->sourceContext->standingsHandler();
        $results = $standingsHandler->fetchStandings($competition_id, $season_id);

        return redirect()->back()->with('success', 'Standings fetched successfully!')->with('standings', $results);
    }

    public function fetchMatches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $competition_id = $request->competition_id;
        $season_id = $request->season_id ?? Season::where('competition_id', $competition_id)
            ->orderBy('start_date', 'desc')
            ->first()
            ->id;

        $is_fixtures = $request->is_fixtures == 1;
        $request->merge(['shallow_fetch' => $request->shallow_fetch ?? false]);

        $matchesHandler = $this->sourceContext->matchesHandler();
        $results = $matchesHandler->fetchMatches($competition_id, $season_id, $is_fixtures);

        return redirect()->back()->with('success', 'Matches fetched successfully!')->with('matches', $results);
    }

    public function fetchMatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $game_id = $request->game_id;
        $request->merge(['ignore_results' => true]);

        $matchesHandler = $this->sourceContext->matchHandler();
        $results = $matchesHandler->fetchMatch($game_id);

        return redirect()->back()->with('success', 'Match fetched successfully!')->with('match', $results);
    }
}
