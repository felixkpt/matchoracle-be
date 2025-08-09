<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Get Source Strategies
        </h2>
    </x-slot>

    <div class="container mx-auto p-8">

        <!-- Display Messages -->
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display Results -->
        @if (session('seasons'))
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Seasons Results</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode(session('seasons'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if (session('standings'))
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Standings Results</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode(session('standings'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if (session('matches'))
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Matches Results</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode(session('matches'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if (session('match'))
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Match Results</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode(session('match'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        <!-- Competition Selection -->
        <label for="competition" class="block text-sm font-medium text-gray-700">Select Competition</label>
        <select id="competition" class="mt-1 block w-full p-2 border-gray-300 rounded-md"
            onchange="loadSeasons(this.value)">
            <option value="">Select a competition</option>
            @foreach ($competitions as $competition)
                <option value="{{ $competition->id }}">{{ $competition->country->name }} -
                    {{ $competition->name }}&nbsp;&nbsp;&nbsp;(#{{ $competition->id }})</option>
            @endforeach
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
        <div class="mt-6 flex flex-wrap gap-4">
            <button onclick="runTest('seasons')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
                Get Seasons
            </button>
            <button onclick="runTest('standings')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
                Get Standings
            </button>
            <button onclick="runTest('matches')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
                Get Matches
            </button>
            <button onclick="runTest('match')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
                Get Match
            </button>
        </div>

    </div>

    <script>
        async function fetchWithAuth(url) {

            return fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('Error:', error);
                    throw error;
                });
        }

        function loadSeasons(competitionId) {
            fetchWithAuth(`/api/dashboard/competitions/view/${competitionId}/seasons?per_page=100`)
                .then(data => {
                    const seasonSelect = document.getElementById('season');
                    seasonSelect.innerHTML = '<option value="">Select a season</option>';
                    data?.results?.data.forEach(season => {
                        const option = document.createElement('option');
                        option.value = season.id;
                        const start = new Date(season.start_date).getFullYear();
                        const end = new Date(season.end_date).getFullYear();
                        option.text = `${start} - ${end}` + (season.is_current ? " (Current)" : "");
                        seasonSelect.add(option);
                    });
                });
        }

        function runTest(job) {
            const source = 'forebet';
            const competitionId = document.getElementById('competition').value;
            const seasonId = document.getElementById('season').value;
            const matchId = document.getElementById('match').value;

            let url = `/sources-test/run?source=${source}&job=${job}&competition_id=${competitionId}`;
            if (seasonId) url += `&season_id=${seasonId}`;
            if (job === 'match' && matchId) url += `&game_id=${matchId}`;

            window.location.href = url;
        }
    </script>
</x-app-layout>
