import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'


const HistoricalResults = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/matches?task=historical_results'
        columns={jobLogsColumns}
        search={true}
        tableId='HistoricalResultsMatchesJoblogsTable'
      />
    </div>
  )
}

export default HistoricalResults