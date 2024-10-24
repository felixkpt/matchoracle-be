import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const TrainPredictionsJobLogs = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/train-predictions'
        columns={jobLogsColumns}
        search={true}
        tableId='TrainPredictionsJobLogs'
      />
    </div>
  )
}

export default TrainPredictionsJobLogs