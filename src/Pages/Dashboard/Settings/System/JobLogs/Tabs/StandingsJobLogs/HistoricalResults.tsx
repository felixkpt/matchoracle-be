import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const HistoricalResults = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/standings?task=historical_results'
        columns={jobLogsColumns}
        search={true}
        tableId='HistoricalResultsStandingsJobLogs'
      />
    </div>
  )
}

export default HistoricalResults