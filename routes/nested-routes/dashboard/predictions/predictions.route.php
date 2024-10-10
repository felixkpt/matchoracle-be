<?php

use App\Http\Controllers\Dashboard\Predictions\PredictionsController;
use Illuminate\Support\Facades\Route;

// Create an instance of the PredictionsController
$controller = PredictionsController::class;

// Routes for different prediction views
Route::get('/', [$controller, 'index'])->name('all predictions'); // All predictions
Route::get('/today', [$controller, 'today'])->name('today\'s predictions'); // Today's predictions
Route::get('/yesterday', [$controller, 'yesterday'])->name('yesterday\'s predictions'); // Yesterday's predictions
Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow\'s predictions'); // Tomorrow's predictions

// Routes for predictions based on year, year/month, and year/month/day
Route::get('/{year}', [$controller, 'year'])->name('year predictions')->where(['year' => '[0-9]+']); // Predictions for a specific year
Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month predictions')->where(['year' => '[0-9]+', 'month' => '[0-9]+']); // Predictions for a specific year and month
Route::get('/{year}/{month}/{day}', [$controller, 'yearMonthDay'])->name('year_month_day predictions')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'day' => '[0-9]+']); // Predictions for a specific year, month, and day

// Updated routes for combined date ranges
Route::get('/{start_year}/{start_month}/{start_day}/to/{end_year}/{end_month}/{end_day}', [$controller, 'dateRange'])
    ->where([
        'start_year' => '[0-9]+', 'start_month' => '[0-9]+', 'start_day' => '[0-9]+',
        'end_year' => '[0-9]+', 'end_month' => '[0-9]+', 'end_day' => '[0-9]+'
    ])
    ->name('date_range'); // Predictions for a specified date range

// Delete prediction route
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy prediction'); // Delete a specific prediction
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)

// Routes for Python app integration
Route::prefix('from-python-app')->group(function () use ($controller) {
    Route::post('/store-predictions', [$controller, 'storePredictions']); // Store predictions from Python app
    Route::post('/store-competition-score-target-outcome', [$controller, 'storeCompetitionPredictionTypeStatistics']); // Store competition score and target outcome from Python app
    Route::post('/predictions-job-logs', [$controller, 'predictionsJobLogs']); // Perform logging from Python app
    Route::post('/update-competition-last-training', [$controller, 'updateCompetitionLastTraining']); // Perform logging from Python app
    Route::post('/update-competition-last-prediction', [$controller, 'updateCompetitionLastPrediction']); // Perform logging from Python app
});
