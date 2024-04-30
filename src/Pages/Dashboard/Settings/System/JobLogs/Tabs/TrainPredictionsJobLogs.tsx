import AutoTable from '@/components/Autos/AutoTable'

const TrainPredictionsJobLogs = () => {

  const columns = [
    { key: 'id' },
    { key: 'prediction_type_id' },
    { key: 'date' },
    { key: 'job_run_counts', label: 'Job Runs', },
    { key: 'competition_run_counts', label: 'Competition runs' },
    { key: 'train_run_counts', label: 'Train runs' },
    { key: 'train_success_counts', label: 'Train success' },
    { key: 'train_failed_counts', label: 'Train failed' },
    { key: 'trained_counts', label: 'Trained counts' },
    { label: 'Last run', key: 'Last_run' },
    { label: 'Created At', key: 'Created_at' },
  ]

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/train-predictions'
        columns={columns}
        search={true}
        tableId='TrainPredictionsJobLogs'
      />
    </div>
  )
}

export default TrainPredictionsJobLogs