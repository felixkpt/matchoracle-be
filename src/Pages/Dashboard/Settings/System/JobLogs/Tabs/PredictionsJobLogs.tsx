import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'


const PredictionsJobLogs = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/predictions'
        columns={jobLogsColumns}
        search={true}
        tableId='PredictionsJobLogs'
      />
    </div>
  )
}
export default PredictionsJobLogs