# MatchOracle Frontend

The **MatchOracle Frontend** is a React-based web application designed for administrators to manage, monitor, and troubleshoot sports data, specifically football statistics and match-related information. The interface provides an interactive dashboard that communicates with the backend via API requests to display key metrics, track tasks, and trigger automation actions.

---

## Key Features

- **Admin Dashboard**:  
  A central hub displaying crucial statistics, including the number of countries, teams, competitions, and key match-related information.

- **Real-Time Data Fetching**:  
  The frontend interacts with the backend through Axios to fetch real-time data about matches, competitions, predictions, and odds. The data is then dynamically updated on the dashboard.

- **Task Management & Automation**:  
  Admins can manually trigger backend tasks such as fetching competition data, match results, and running predictions. Tasks are displayed in the UI with status indicators for job completion, success, and failure rates.

- **Advanced Insights & Monitoring**:  
  Monitor the success of automation tasks, track estimated completion times, and view detailed logs for task troubleshooting and system performance.

- **Data Inspection**:  
  Inspect and explore detailed information on countries, continents, competitions, teams, seasons, and historical data to help with debugging and managing data integrity.

- **Error Handling & UI Feedback**:  
  Graceful error handling ensures the user is informed if data cannot be fetched or processed, with appropriate messages displayed when issues arise.

- **Responsive & Interactive UI**:  
  The frontend is built with an intuitive, user-friendly design that adapts seamlessly to different screen sizes.

---

## Installation & Setup

To run the **MatchOracle Frontend** locally, follow these steps:

### Prerequisites

- **Node.js** (v18.x or later recommended)
- **npm** or **Yarn**

### Steps

1. Clone the repository:

   ```bash
   git clone git@github.com:felixkpt/matchoracle-fe.git
   ```

2. Install dependencies:

   ```bash
   cd matchoracle-fe
   npm install
   # or with yarn
   yarn install
   ```

3. Configure environment variables:  
   Create a `.env` file (if not already present) and add the following API URLs:

   ```env
   VITE_APP_BASE_API=http://localhost  # Laravel backend URL
   ```

4. Start the development server:

   ```bash
   npm start
   # or with yarn
   yarn start
   ```

   The frontend should now be running at [http://localhost:5173](http://localhost:5173) (or another available port).

---

## Project Structure

The project is organized as follows:

- **`src/`**: Core source files for the application.
  - **`components/`**: Reusable UI components.
  - **`pages/`**: Main pages such as the dashboard, settings, and data inspection.
  - **`hooks/`**: Custom hooks (e.g., `useAxios`) for managing API requests.
  - **`utils/`**: Utility functions and configuration helpers.
  - **`services/`**: API service for backend communication.
  - **`contexts/`**: State management using React Context API.

---

## Key Pages & Components

- **Dashboard Page**:  
  Displays key statistics, including information about countries, teams, competitions, and matches.

- **Admin Controls**:  
  Interface to trigger backend jobs like fetching competition data, match results, odds, and predictions.

- **Task Status Monitoring**:  
  Allows admins to track task progress and view logs for success, failure, and completion times.

- **Loader**:  
  A loading spinner that is displayed while data is being fetched from the backend.

- **Error Messages**:  
  Displays user-friendly error messages when data cannot be loaded or processed.

---

## Handling Automation Tasks

The frontend allows administrators to manually trigger specific tasks. Each action corresponds to a job that can be triggered from the UI, on competition level:

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

Each of these actions has a corresponding UI button, and admins can see the status of each task as it's executed.

---

## Authentication

If your frontend requires authentication, make sure to implement the login flow by connecting the frontend to Laravel’s authentication system (sanctum/session-based).

---

## Communication with the Backend

The frontend communicates with the backend using the following endpoints:

- **GET `/dashboard/stats`**: Fetches basic dashboard statistics like countries, competitions, teams, etc.
- **GET `/dashboard/advanced-stats`**: Fetches detailed match and performance statistics.
- **GET `/dashboard/matches`**: Retrieves data for past and upcoming matches.

API requests are handled using custom Axios hooks that manage loading states and error handling.

---

## Deployment

### Production Build

1. Create a production build:

   ```bash
   npm run build
   # or with yarn
   yarn build
   ```

2. Deploy the build to a static hosting platform (e.g., Vercel, Netlify, or your own server).

### Backend Configuration

Ensure that the frontend is connected to the correct backend APIs by updating the `.env` file with the appropriate URLs:

```env
VITE_APP_BASE_API=https://your-laravel-backend.com
```

---

## Troubleshooting

- **CORS issues**:  
   Ensure that the backend allows cross-origin requests by properly configuring CORS settings in both FastAPI and Laravel.

- **API Errors**:  
   Check for issues in the console related to API requests and verify that the API URLs in the `.env` file are correct.

- **Frontend Issues**:  
   Ensure all dependencies are installed correctly and that the development server is running without errors.

---

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a Pull Request.

---

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

## Author

**Felix Kiptoo Biwott**  

## Repository Links
- [GitHub Repository (Laravel Backend)](https://github.com/felixkpt/matchoracle-be)  
- [GitHub Repository (Frontend)](https://github.com/felixkpt/matchoracle-fe)

---