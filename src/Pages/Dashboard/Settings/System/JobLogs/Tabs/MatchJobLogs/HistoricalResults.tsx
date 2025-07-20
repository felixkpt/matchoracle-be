import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const HistoricalResults = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/match?task=historical_results'
        columns={jobLogsColumns}
        search={true}
        tableId='HistoricalResultsMatchJoblogsTable'
      />
    </div>
  )
}

export default HistoricalResults