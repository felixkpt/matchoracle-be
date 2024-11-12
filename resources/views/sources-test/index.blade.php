@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Test Source Strategies</h1>

    <!-- Competition Selection -->
    <label for="competition" class="block text-sm font-medium text-gray-700">Select Competition</label>
    <select id="competition" class="mt-1 block w-full p-2 border-gray-300 rounded-md" onchange="loadSeasons(this.value)">
        <option value="">Select a competition</option>
    </select>

    <!-- Season Selection -->
    <label for="season" class="block mt-4 text-sm font-medium text-gray-700">Select Season</label>
    <select id="season" class="mt-1 block w-full p-2 border-gray-300 rounded-md">
        <option value="">Select a season</option>
    </select>

    <!-- Match Selection -->
    <label for="match" class="block mt-4 text-sm font-medium text-gray-700">Select Match (optional)</label>
    <select id="match" class="mt-1 block w-full p-2 border-gray-300 rounded-md">
        <option value="">Select a match</option>
    </select>

    <!-- Action Links -->
    <div class="mt-6 space-y-4">
        <a href="#" onclick="runTest('seasons')" class="text-blue-500 hover:underline">Test Seasons</a>
        <a href="#" onclick="runTest('standings')" class="text-blue-500 hover:underline">Test Standings</a>
        <a href="#" onclick="runTest('matches')" class="text-blue-500 hover:underline">Test Matches</a>
        <a href="#" onclick="runTest('match')" class="text-blue-500 hover:underline">Test Match</a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", loadCompetitions);

function loadCompetitions() {
    fetch('/api/admin/competitions')
        .then(response => response.json())
        .then(data => {
            const competitionSelect = document.getElementById('competition');
            data.forEach(competition => {
                const option = document.createElement('option');
                option.value = competition.id;
                option.text = competition.name;
                competitionSelect.add(option);
            });
        });
}

function loadSeasons(competitionId) {
    fetch(`/api/admin/competitions/${competitionId}/seasons`)
        .then(response => response.json())
        .then(data => {
            const seasonSelect = document.getElementById('season');
            seasonSelect.innerHTML = '<option value="">Select a season</option>';
            data.forEach(season => {
                const option = document.createElement('option');
                option.value = season.id;
                option.text = season.name;
                seasonSelect.add(option);
            });
        });
}

function runTest(job) {
    const source = 'forebet'; // Or make this selectable if you have multiple sources
    const competitionId = document.getElementById('competition').value;
    const seasonId = document.getElementById('season').value;
    const matchId = document.getElementById('match').value;

    let url = `/sources-test/test?source=${source}&job=${job}&competition_id=${competitionId}`;
    if (seasonId) url += `&season_id=${seasonId}`;
    if (job === 'match' && matchId) url += `&game_id=${matchId}`;

    window.location.href = url;
}
</script>
@endsection
