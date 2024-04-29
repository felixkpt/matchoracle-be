import AutoTable from '@/components/AutoTable'

type Props = {}

const PredictionsJobLogs = (props: Props) => {

  const columns = [
    { key: 'id' },
    { key: 'prediction_type_id' },
    { key: 'date' },
    { key: 'job_run_counts', label: 'Job Runs', },
    { key: 'competition_run_counts', label: 'Competition runs' },
    { key: 'prediction_run_counts', label: 'Prediction runs' },
    { key: 'prediction_success_counts', label: 'Prediction success' },
    { key: 'prediction_failed_counts', label: 'Prediction failed' },
    { key: 'predicted_counts', label: 'Predicted matches' },
    { label: 'Last run', key: 'Last_run' },
    { label: 'Created At', key: 'Created_at' },
  ]

  return (
    <div>
      <AutoTable
        baseUri='/admin/settings/system/job-logs/predictions'
        columns={columns}
        search={true}
        tableId='PredictionsJobLogs'
      />
    </div>
  )
}

export default PredictionsJobLogs