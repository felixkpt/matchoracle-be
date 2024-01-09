<?php

namespace App\Services\Games;

use App\Models\Competition;
use App\Models\CompetitionsLog;
use App\Models\RecentCompetitionDetailedFetch;
use App\Models\RecentTeamDetailedFetch;
use App\Models\Team;
use App\Models\TeamsLog;
use App\Repositories\TeamRepository;
use App\Services\Games\Traits\DBActions;
use App\Services\Games\Traits\Source1;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Games
{

    use DBActions;
    use Source1;
    private $repo;

    protected $existing_competition;

    public function __construct()
    {
        $this->repo = new TeamRepository();
    }

    /**
     * 
     * This function sits in between DBActions and any Source
     * 
     * */
    function detailedFixture($id, $autoModel, $is_competition = false, $ignore_results_status = false, $existing_competition = null)
    {
        $this->existing_competition = $existing_competition;
        $table = $autoModel->getTable();

        $chunk = 3;
        if (isset(request()->limit) && request()->limit < $chunk)
            $chunk = request()->limit;

        $all_res = [];
        $autoModel
            ->whereNotNull($table . '.url')
            ->when($is_competition === true, fn ($q) => $q->where('competition_id', $id))
            ->when($is_competition === false, fn ($q) => $q->where('home_team_id', $id)->orwhere('away_team_id', $id))
            ->when($ignore_results_status === false, fn ($q) => $q->where('results_status', 0))
            ->leftjoin('competitions', $table . '.competition_id', 'competitions.id')
            ->leftjoin('countries', 'competitions.country_id', 'countries.id')
            ->select(
                $table . '.*',
                'competitions.name as competition',
                'countries.name as country',
            )
            ->orderby('updated_at', 'asc')
            ->chunk($chunk, function ($games) use (&$all_res, $table) {

                $res = [];
                foreach ($games as $game) {
                    // Stop chunk processing of limit is supplied and reached
                    if (request()->limit && count($all_res) >= request()->limit)
                        return false;

                    $game = $game->toArray();
                    $game['table'] = $table;
                    $fetch_details = $this->doDetailedFixture($game);

                    $res[] = [
                        'fixture' => '(#' . $game['id'] . ')',
                        'competition' => $game['competition'] ?? null,
                        'country' => $game['country'] ?? null,
                        'teams' => ['home_team_id' => $game['home_team_id'], 'away_team_id' => $game['away_team_id']],
                        'fetch_details' => $fetch_details
                    ];

                    $year = Str::before($table, '_games');
                    $this->updateTeamLastFetch($year, $game['home_team_id'], 'detailed_fetch', $fetch_details);
                }

                $all_res = array_merge($all_res, $res);
            });

        return $all_res;
    }


    /**
     * updateTeamLastFetch
     * @param mixed $team_id
     * @return void
     */
    function updateTeamLastFetch($year = null, $team_id, $fetch_type = 'fetch', $fetch_details = null)
    {
        $fetch_type = Str::after($fetch_type, 'last_');

        try {
            // Start the database transaction
            DB::beginTransaction();

            Team::find($team_id)->update(['last_' . $fetch_type => Carbon::now()]);

            $team_logs = TeamsLog::where('team_id', $team_id)->first();
            $fetch_details = json_encode($fetch_details);

            if ($team_logs) {
                $team_logs->{$fetch_type . '_counts'} = $team_logs->{$fetch_type . '_counts'} + 1;
                $team_logs->update([
                    $fetch_type . '_counts' => $team_logs->{$fetch_type . '_counts'},
                    $fetch_type . '_details' => $fetch_details,
                ]);
            } else {
                TeamsLog::create([
                    'team_id' => $team_id,
                    $fetch_type . '_counts' => 1,
                    $fetch_type . '_details' => $fetch_details,
                ]);
            }

            $arr = ['team_id' => $team_id, 'fetched_at' => Carbon::now()];
            if ($fetch_type === 'detailed_fetch') {
                $arr['year'] = $year;
                $exists = RecentTeamDetailedFetch::where([['year', $year], ['team_id', $team_id]])->first();
                if (!$exists)
                    RecentTeamDetailedFetch::create($arr);
            }

            // Commit the database transaction
            DB::commit();
        } catch (QueryException $e) {
            // Handle the database query exception
            DB::rollback(); // Rollback the transaction

            // Log or handle the error as per your requirement
            // You can access the error message using $e->getMessage()

            // Example: Log the error
            Log::error('Error updating team last fetch: ' . $e->getMessage());

            // Rethrow the exception to propagate it further if needed
            throw $e;
        } catch (\Exception $e) {
            // Handle any other exception that may occur
            DB::rollback(); // Rollback the transaction

            // Log or handle the error as per your requirement
            // You can access the error message using $e->getMessage()

            // Example: Log the error
            Log::error('Error updating team last fetch: ' . $e->getMessage());

            // Rethrow the exception to propagate it further if needed
            throw $e;
        }
    }


    /**
     * updateCompetitionLastFetch
     * @param mixed $competition_id
     * @return void
     */
    function updateCompetitionLastFetch($year = null, $competition_id, $fetch_type = 'fetch', $fetch_details = null)
    {
        $fetch_type = Str::after($fetch_type, 'last_');

        try {
            // Start the database transaction
            DB::beginTransaction();

            Competition::find($competition_id)->update(['last_' . $fetch_type => Carbon::now()]);

            $team_logs = CompetitionsLog::where('competition_id', $competition_id)->first();
            $fetch_details = json_encode($fetch_details);

            if ($team_logs) {
                $team_logs->{$fetch_type . '_counts'} = $team_logs->{$fetch_type . '_counts'} + 1;
                $team_logs->update([
                    $fetch_type . '_counts' => $team_logs->{$fetch_type . '_counts'},
                    $fetch_type . '_details' => $fetch_details,
                ]);
            } else {
                CompetitionsLog::create([
                    'competition_id' => $competition_id,
                    $fetch_type . '_counts' => 1,
                    $fetch_type . '_details' => $fetch_details,
                ]);
            }

            $arr = ['competition_id' => $competition_id, 'fetched_at' => Carbon::now()];
            if ($fetch_type === 'detailed_fetch') {
                $arr['year'] = $year;
                $exists = RecentCompetitionDetailedFetch::where([['year', $year], ['competition_id', $competition_id]])->first();
                if (!$exists)
                    RecentCompetitionDetailedFetch::create($arr);
            }

            // Commit the database transaction
            DB::commit();
        } catch (QueryException $e) {
            // Handle the database query exception
            DB::rollback(); // Rollback the transaction

            // Log or handle the error as per your requirement
            // You can access the error message using $e->getMessage()

            // Example: Log the error
            Log::error('Error updating team last fetch: ' . $e->getMessage());

            // Rethrow the exception to propagate it further if needed
            throw $e;
        } catch (\Exception $e) {
            // Handle any other exception that may occur
            DB::rollback(); // Rollback the transaction

            // Log or handle the error as per your requirement
            // You can access the error message using $e->getMessage()

            // Example: Log the error
            Log::error('Error updating team last fetch: ' . $e->getMessage());

            // Rethrow the exception to propagate it further if needed
            throw $e;
        }
    }
}
