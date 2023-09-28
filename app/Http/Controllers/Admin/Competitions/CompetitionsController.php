<?php

namespace App\Http\Controllers\Admin\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use App\Repositories\SearchRepo;
use App\Services\Client;
use App\Services\Common;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CompetitionsController extends Controller
{

    function index()
    {
        return response('Competitions/Index');
    }

    function list() {
       
        $countries = Competition::query();

        $res = SearchRepo::of($countries, ['name'], ['countries.name'])->orderby('countries.priority_no')->paginate();

        return response(['results' => $res]);

    }

    function create()
    {
        return response('Competitions/Create');
    }

    function store()
    {
        request()->validate([
            'source' => 'required:url'
        ]);

        $source = request()->source;
        $is_domestic = request()->is_domestic;

        if ($is_domestic) {
            $source = rtrim($source, '/');
            if (!Str::endsWith($source, '/standing'))
                $source .= '/standing';
        }

        $source = parse_url($source);

        $source = $source['path'];

        $suff = '/standing';
        if (Str::endsWith($source, $suff)) {
            $source = Str::beforeLast($source, $suff);
        } else {
            if (Client::status(Common::resolve($source . $suff)) === 200)
                $is_domestic = true;
        }

        $exists = Competition::where('url', $source)->first();
        if ($exists)
            return response(['results' => ['message' => 'Whoops! It seems the competition is already saved (#' . $exists->id . ').']]);

        if (!Country::count())
            return response(['results' => ['message' => 'Countries list is empty.']]);

        $country = Common::saveCountry($source);

        $html = Client::request(Common::resolve($source));

        if ($html === null) return;

        $crawler = new Crawler($html);

        $competition = $crawler->filter('h1.frontH')->each(fn (Crawler $node) => ['src' => $source, 'name' => $node->text(), 'img' => $node->filter('img')->attr('src')]);
        $competition = $competition[0] ?? null;

        $competition = Common::saveCompetition($competition, null, $is_domestic);

        if ($competition)
            return Common::updateCompetitionAndHandleTeams($competition, $country, $is_domestic, null, false);
        else
            return response(['results' => ['message' => 'Cannot get competition.']]);
    }
}
