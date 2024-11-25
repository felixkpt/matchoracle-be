# MatchOracle Backend

**MatchOracle** is a comprehensive backend application designed to manage sports-related data, including match data, odds, seasons, standings, statistics, and predictions. Built with Laravel and integrated with a Python-based machine learning (ML) service, MatchOracle automates various tasks, such as data fetching, processing, and predictions. It also includes a React-based admin frontend for easy management, debugging, and monitoring.

---

## Features

- **Match & Odds Scheduling**: Automates the fetching and processing of historical results, recent results, shallow fixtures, and full fixtures for matches and odds.
- **Seasons & Standings Scheduling**: Regularly updates seasons and standings data at optimized intervals, ensuring fresh and accurate information.
- **Statistics & Predictions Scheduling**: Handles the periodic update of competition statistics and the generation of predictions.
- **Dynamic Timing Strategy**: Scheduling is dynamically adjusted based on active competitions, data sources, database size, and system performance to ensure reliability.
- **Admin Frontend Integration**: Allows administrators to manually trigger scheduled tasks, monitor the automation process, and troubleshoot issues. Includes insights and status dashboards.

---

## Installation

### Prerequisites

- PHP 8.x or higher
- Composer
- Laravel 9.x or higher
- MySQL or another compatible database

### Steps

1. **Clone the repository**:  
   ```bash
   git clone git@github.com:felixkpt/matchoracle-be.git
   ```
2. **Install dependencies**:  
   ```bash
   composer install
   ```
3. **Set up environment variables**:  
   Copy the example environment configuration and modify it to suit your setup:
   ```bash
   cp .env.example .env
   ```
   Adjust the database and other settings in the `.env` file.
   
4. **Run migrations**:  
   Migrate the database to create necessary tables:
   ```bash
   php artisan migrate
   ```

---

## Admin Frontend Integration

The backend integrates with a React-based admin frontend, providing easy access for managing and monitoring data. 

### Use Cases:
1. **Manual Automation Triggers**:  
   Administrators can manually trigger scheduled jobs in case of delays or to fetch updated source data.

2. **Troubleshooting**:  
   Admins have access to debugging tools that allow them to inspect data structures, such as countries, competitions, seasons, standings, teams, and matches.

3. **Monitoring and Insights**:  
   - General app status and data dashboard.
   - Automation dashboard to monitor job runs, success rates, and estimated completion times for various tasks.

### Seeder-based Data Initialization:
- **ContinentsTableSeeder**: Adds continents to the system, necessary for competition setup.
- **CountriesSeeder**: Adds countries and links them to the appropriate continents.
- **CompetitionSeeder**: Dynamically fetches and seeds competition data using a configurable game source strategy.
- **AppSettingSeeder**: Initializes important application settings such as thresholds and API keys.

### Competitions & Predictions Workflow:
1. **Activate Competitions**:  
   Administrators can activate specific competitions for processing, which includes fetching and updating related data such as seasons, standings, teams, and matches.

2. **Python ML Integration**:
   - The Laravel application communicates with a FastAPI service for ML predictions.
   - FastAPI processes the competition data, trains models, and sends predictions back to the backend.
   - The Laravel app polls FastAPI to ensure that job completion is processed before continuing with the next task.

---

## Automation Scheduling

### Console Command Scheduling
Laravel's built-in Scheduler manages periodic tasks. These tasks are defined in `app/Console/Kernel.php` and are executed automatically according to the defined schedule.

#### Automation Folder Structure
Key command highlights:
- **Seasons**: `app:seasons-handler` – Manages season updates.
- **Standings**: `app:standings-handler` – Updates standings data for both historical and recent results.
- **Matches**: `app:matches-handler` – Fetches and processes match-related data.
- **Competition Statistics**: `app:competition-statistics` – Generates statistics related to ongoing competitions.
- **TrainPredictions**: `app:train-predictions-handler` – Triggers the training of predictions on the Fast API.
- **Predictions**: `app:predictions-handler` – Triggers the generation of predictions on the Fast API.

### Automation Actions Extended
The frontend provides manual triggers for the following actions on competition level:

| **Action**                              | **Description**                           |
|-----------------------------------------|-------------------------------------------|
| `abbreviation_last_fetch`               | Fetch competition abbreviations.          |
| `seasons_last_fetch`                    | Fetch season data.                        |
| `standings_recent_results_last_fetch`   | Fetch recent standings data.             |
| `standings_historical_results_last_fetch`| Fetch historical standings data.         |
| `matches_recent_results_last_fetch`     | Fetch recent match results.              |
| `matches_historical_results_last_fetch` | Fetch historical match results.          |
| `matches_fixtures_last_fetch`           | Fetch full match fixtures.               |
| `matches_shallow_fixtures_last_fetch`   | Fetch shallow match fixtures.             |
| `odd_recent_results_last_fetch`         | Fetch recent odds data.                  |
| `odd_historical_results_last_fetch`     | Fetch historical odds data.              |
| `odd_fixtures_last_fetch`               | Fetch full odds fixtures.                |
| `odd_shallow_fixtures_last_fetch`       | Fetch shallow odds fixtures.             |
| `predictions_last_train`                | Train prediction models.                 |
| `predictions_last_done`                 | Run predictions.                         |
| `stats_last_done`                       | Generate competition statistics.         |
| `predictions_stats_last_done`           | Generate prediction statistics.          |

---

## Scheduled Commands

### Seasons Commands
| Task            | Command              | Frequency        | Offset (Minutes) |
|-----------------|----------------------|------------------|------------------|
| Update Seasons  | `app:seasons-handler` | Every 4 hours    | 0                |

### Predictions Commands
| Task                   | Command                            | Frequency        | Offset (Minutes) |
|------------------------|------------------------------------|------------------|------------------|
| Predictions Handler    | `app:predictions-handler`         | Every 2 hours    | 45               |
| Train Predictions      | `app:train-predictions-handler`   | Every 2 hours    | 0                |

### Matches Commands
| Task                   | Command                                        | Frequency       | Offset (Minutes) |
|------------------------|------------------------------------------------|-----------------|------------------|
| Historical Results     | `app:matches-handler --task=historical_results` | Every 2 hours   | 15               |
| Recent Results         | `app:matches-handler --task=recent_results`     | Every 3 hours   | 0                |
| Shallow Fixtures       | `app:matches-handler --task=shallow_fixtures`   | Every 2 hours   | 18               |
| Fixtures               | `app:matches-handler --task=fixtures`           | Every 6 hours   | 20               |

### Odds Commands
| Task                   | Command                                        | Frequency       | Offset (Minutes) |
|------------------------|------------------------------------------------|-----------------|------------------|
| Historical Results     | `app:odd-handler --task=historical_results`    | Every 3 hours   | 29               |
| Recent Results         | `app:odd-handler --task=recent_results`        | Every 6 hours   | 0                |
| Shallow Fixtures       | `app:odd-handler --task=shallow_fixtures`      | Every 3 hours   | 34               |
| Fixtures               | `app:odd-handler --task=fixtures`              | Twice daily     | -                |

---

## Timing Strategy

The timing of these commands is influenced by several factors:
- **Active Competitions Count**: A higher number of active competitions can affect how frequently tasks are executed.
- **Data Source Frequency**: Different data sources may have varying refresh rates, which can adjust scheduling.
- **Database Size**: A larger dataset may result in longer processing times and thus require adjusted intervals.
- **Application Performance**: The system is designed to optimize performance while avoiding overload by adjusting intervals based on load & resource availability.

---

## Python ML Integration

- **Laravel App**: Responsible for initiating communication with the FastAPI service.
- **FastAPI**:
  - FastAPI is responsible for training machine learning models using historical data and predicting match outcomes.
  - Predictions are sent back to Laravel for database storage and further processing.

---

## Configuration Notes

### Scheduler
- The task scheduling is configured in `app/Console/Kernel.php`.
- Custom command files (`matches_commands.php`, `odds_commands.php`) are used to define specific tasks for matches and odds.

### Modifying the Schedule

To modify the schedule or adjust the timings of commands, you can update the corresponding settings in the `matches_commands.php` and `odds_commands.php` files located in the `app/Console` directory.

---

### Dynamic Game Source Strategy
- Switch between different game data providers easily using the `GameSourceStrategy` configuration.

---

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

## Author

**Felix Kiptoo Biwott**  
[GitHub Repository (Laravel Backend)](https://github.com/felixkpt/matchoracle-be)  
[GitHub Repository (FastAPI Predictions)](https://github.com/felixkpt/matchoracle-predictions)  
[GitHub Repository (React Frontend)](https://github.com/felixkpt/matchoracle-fe)  

---