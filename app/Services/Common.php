<?php

namespace App\Services;

use App\Models\CompetitionAbbreviation;
use App\Models\Country;
use App\Models\Stadium;
use App\Models\WeatherCondition;
use App\Repositories\CompetitionRepository;
use App\Repositories\EloquentRepository;
use App\Repositories\TeamRepository;
use App\Services\Games\Games;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class Common
{
    public static function TeamRepo()
    {
        return new TeamRepository();
    }
    public static function CompetitionRepo()
    {
        return new CompetitionRepository();
    }

    /**
     * Update competition and handle associated teams.
     *
     * @param \App\Models\Competition $competition The competition object to update.
     * @param string $country The country associated with the competition.
     * @param bool $isDomestic Indicates whether the competition is domestic or not.
     * @param mixed|null $crawler (optional) The crawler object used for web scraping. If not provided, it will be created internally.
     * @param mixed|null $responseType (optional) The response type for the API response. If not provided, the default will be used.
     * @param bool $ignoreLastFetch (optional) Whether to ignore the last fetch date and force updating. Defaults to false.
     * 
     * @return mixed The API response with the updated competition and associated teams.
     */
    static function updateCompetitionAndHandleTeams($competition, $country, $is_domestic, $crawler = null, $response_type = null, $ignore_last_fetch = false)
    {

        $last_fetch = Carbon::createFromDate($competition->last_fetch);
        $now = Carbon::now();
        $testdate = $last_fetch->diffInDays($now);

        if ($ignore_last_fetch === false && $competition->last_fetch !== null && $testdate < 1)
            return response(['results' => ['type' => 'success', 'message' => 'Last fetch is less than 1 day.']], 200, $response_type);

        if (!$crawler) {
            $html = Client::request(Common::resolve($competition->url));

            if ($html === null) return;

            $crawler = new Crawler($html);
        }

        $teams = $crawler->filter('table.standings')->first()->filter('tr:not(.heading)')->each(function (Crawler $node) {

            $row = $node->filter('td')->each(function (Crawler $node, $i) {
                $arr = ['priority_no', 'team', 'points', 'games_played', 'won', 'draw', 'lost', 'goals_for', 'goals_for', 'goal_difference'];

                $data = $node->text();
                if ($i === 1)
                    $data = ['name' => $node->text(), 'url' => $node->filter('a')->attr('href')];

                return [$arr[$i] => $data];
            });

            return $row;
        });

        $fetch_details = 'Competition created.';
        if ($competition->action === 'updated')
            $fetch_details = 'Competition updated.';

        $added = Common::saveTeams($teams, $competition, $country, $is_domestic, $competition->action === 'created' ? false : true);

        $removed = self::teamRepo()->model->where('competition_id', $competition->id)->whereNotIn('id', array_column($added, 'id'));
        $removedTeams = $removed->get(['id', 'name']);
        // Removing teams not currently listed under this competition on the source, but are on the DB
        if ($removed->count() > 0)
            $removed->update(['competition_id' => null]);

        if ($competition->action === 'updated') (new Games())->updateCompetitionLastFetch(null, $competition->id, 'last_fetch', $fetch_details);


        return response(['results' => ['competition' => $competition->toArray(), 'teams' => $added, 'removedTeams' => $removedTeams]], 200, $response_type);
    }

    static function saveCompetition(string|array $source, string $name = null, $is_domestic = null)
    {

        $img = null;
        if (is_array($source)) {
            ['src' => $source, 'name' => $name, 'img' => $img] = $source;
        }

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $country = self::saveCountry($source);

        if (!$country)
            return;

        $source = preg_replace('#/+#', '/', '/' . $source);

        $competition = self::competitionRepo()->model->where('country_id', $country->id)->where('url', $source)->first();
        if ($competition) {
            $competition->action = 'updated';
            return $competition;
        }
        $name_init = $name;

        if ($img === null) {
            $html = Client::request(self::resolve($source));

            if ($html === null) return;

            $crawler = new Crawler($html);

            $header = $crawler->filter('.contentmiddle h1.frontH');

            $img = $header->filter('img')->attr('src');
            $name = $header->filter('span')->text();
        }

        if (!preg_match('#' . $name_init . '#i', $name))
            return false;

        $name = preg_replace('#Free football predictions for ' . $country->name . ' #i', '', $name);
        $name = preg_replace('#Free football predictions for#i', '', $name);

        $name = preg_replace('#Standings ' . $country->name . ' #i', '', $name);
        $name = preg_replace('#Standings#i', '', $name);

        $name = trim(preg_replace('# table$#i', '', $name));

        $arr = [
            'name' => $name,
            'slug' => Str::slug($name),
            'country_id' => $country->id,
            'url' => $source,
            'status' => 1,
            'is_domestic' => $is_domestic
        ];

        $competition = self::competitionRepo()->model->where([
            'name' => $name,
            'url' => $source,
            'country_id' => $country->id,
        ])->first();

        if (!$competition) {
            $arr = array_merge($arr, [
                'last_fetch' => '1970-01-01 00:00:00',
                'last_detailed_fetch' => '1970-01-01 00:00:00',
            ]);

            $competition = self::competitionRepo()->model->create($arr);
            $competition->action = 'created';
        } else {
            $competition->update($arr);
            $competition->action = 'updated';
        }

        $res = self::saveCompetitionLogo($img, $country, $competition);
        if ($res) {
            self::competitionRepo()->update($competition->id, ['img' => $res]);
            $competition->img = $res;
        }

        return $competition;
    }


    static function saveCompetitionLogo($source, $country, $competition)
    {
        // Log::info('savecompelogo::', [$source, $country->toArray()]);

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "c" . $competition->id . '.' . $ext;

        $dest = "public/images/competitions/" . $country->slug . '/' . $filename; /* Complete path & file name */

        if (Client::downloadFileFromUrl($source, $dest))
            return $dest;
        else
            return null;
    }

    static function resolve($source)
    {
        return preg_replace("#(?<!:)/+#im", "/", env('SOURCE_SITE') . '/' . $source);
    }

    static function saveCountry($source)
    {
        $slug = self::getCountrySlug($source);
        $repo = new EloquentRepository(Country::class);
        $country = $repo->model->where('slug', $slug)->first();


        if (!$country && $slug) {
            Log::info('Country not found:', ['slug' => $slug]);
        }

        if ($country && $country->has_competitions == 0)
            $repo->model->find($country->id)->update(['has_competitions' => true]);

        return $country;
    }

    static function saveStadium($name, $location = null, $team_id = null)
    {
        if (strlen($name) < 1)
            return null;

        $repo = new EloquentRepository(Stadium::class);

        $arr = [
            'name' => $name,
            'location' => $location,
            'team_id' => $team_id,
        ];

        $res = $repo->updateOrCreate($arr, $arr);

        return $res;
    }

    static function saveWeatherCondition($source_img)
    {
        if (strlen($source_img) < 1)
            return null;

        $repo = new EloquentRepository(WeatherCondition::class);

        $res = $repo->model->where('source_img', $source_img)->first();

        if (!$res) {
            $res = $repo->create([
                'name' => Str::title($source_img),
                'slug' => Str::slug($source_img),
                'source_img' => $source_img
            ]);
        }

        return $res;
    }

    static function saveTeams($teams, $competition, $country, $is_domestic, $is_fetch = false)
    {
        $added = [];
        foreach ($teams as $team) {

            ['name' => $name, 'url' => $url] = $team[1]['team'];

            $team = Common::saveTeam($name, $url, $competition, $country, $is_domestic, $is_fetch);

            $arr = [
                'action' => $team->action,
                'id' => $team->id,
                'name' => $team->name,
                'country' => $country->name ?? 'N/A',
                'competition' => $competition->name ?? 'N/A',
            ];

            $added[] = $arr;
        }

        return $added;
    }
    static function saveTeam($name, $url, $competition, $country, $is_domestic, $is_fetch = false)
    {
        $data = [
            'name' => $name,
            'slug' => Str::slug($name),
            'url' => $url,
            'img' => '',
            'status' => 1,
            "updated_at" => date('Y-m-d H:i:s'),
            'country_id' => $country->id,
        ];

        if ($is_domestic === true)
            $data = array_merge($data, [
                'competition_id' => $competition->id,
            ]);

        $res = self::teamRepo()->model->where([['name', $name], ['url', $url]])->first();

        if (!$res) {
            $data = array_merge($data, [
                'last_fetch' => '1970-01-01 00:00:00',
                'last_detailed_fetch' => '1970-01-01 00:00:00',
            ]);

            $res = self::teamRepo()->model->create($data);
            $res->action = 'created';
        } else
            $res->action = 'updated';

        $fetch_details = 'Team created';
        if ($res->action == 'updated') {
            $fetch_details = 'Updated created';
            if ($is_fetch) (new Games())->updateTeamLastFetch(null, $res->id, 'fetch', $fetch_details);
        }

        return $res;
    }
    static function saveTeamLogo($team_id, $source)
    {
        $exists = self::teamRepo()->findById($team_id);
        if ($exists->img)
            return true;

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "t" . $team_id . '.' . $ext;

        $dest = "public/images/teams/" . $filename; /* Complete path & file name */

        if (Client::downloadFileFromUrl($source, $dest)) {
            self::teamRepo()->update($team_id, ['img' => $dest]);
            return true;
        }

        return false;
    }


    static function saveCompetitionAbbreviation($competition_abbreviation)
    {
        $repo = new EloquentRepository(CompetitionAbbreviation::class);

        $res = $repo->model->where('name', $competition_abbreviation)->first();
        if (!$res)
            $res = $repo->create(['name' => $competition_abbreviation]);
        else
            $res->created = true;

        return $res;
    }

    static function getCountrySlug($source)
    {
        $parts = explode('/', trim($source, '/'));
        $slug = $parts[1];

        if (preg_match('#tips-and-predictions-for-russia-#', $slug))
            $slug = 'russia';
        elseif (preg_match('#^football-tips-and-predictions-for-#', $slug))
            $slug = preg_replace('#^football-tips-and-predictions-for-#', '', $slug);
        elseif (preg_match('#^tips-and-predictions-for-#', $slug))
            $slug = preg_replace('#^tips-and-predictions-for-#', '', $slug);
        elseif (preg_match('#^predictions-#', $slug))
            $slug = preg_replace('#^predictions-#', '', $slug);
        else {
            $m = 'Cannot get country from slug';
            Log::info($m . ':', ['source' => $source]);
            return false;
        }

        $slug = preg_replace('#-1-hnl$#i', '', $slug);
        $slug = preg_replace('#-j-league$#i', '', $slug);
        $slug = preg_replace('#-divizia-a$#i', '', $slug);
        $slug = preg_replace('#-veikkausliiga$#i', '', $slug);
        $slug = preg_replace('#-s-league$#i', '', $slug);
        $slug = preg_replace('#-meistriliiga$#i', '', $slug);
        $slug = preg_replace('#-major-league-soccer$#i', '', $slug);
        $slug = preg_replace('#-tippeligaen$#i', '', $slug);
        $slug = preg_replace('#-superliga|-super-league$#i', '', $slug);
        $slug = preg_replace('#-ekstraklasa$#i', '', $slug);
        $slug = preg_replace('#-gambrinus-liga$#i', '', $slug);
        $slug = preg_replace('#^usa$#i', 'united-states', $slug);
        $slug = preg_replace('#^czech-rep$#i', 'czech-republic', $slug);

        return $slug;
    }

    static function checkCompetitionAbbreviation($games_table)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = autoModel($table);

        if ($game->wherenull('competition_id')->count() > 0) {

            $repo = new EloquentRepository(CompetitionAbbreviation::class);
            $repo->model->all();

            $game->join('competition_abbreviations', $games_table . '.competition_abbreviation', 'competition_abbreviations.name')
                ->wherenull($games_table . '.competition_id')
                ->wherenotnull('competition_abbreviations.competition_id')
                ->select(
                    'competition_abbreviations.name as competition_abbreviation',
                    'competition_abbreviations.competition_id',
                )
                ->chunk(10, function ($games) use ($table) {
                    foreach ($games as $game) {
                        $gamet = autoModel($table);
                        $gamet->where('competition_abbreviation', $game->competition_abbreviation)->update(['competition_id' => $game->competition_id]);
                    }
                });
        }
    }
}
