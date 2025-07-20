import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const SeasonsJobLogs = () => {

  return (
    <div>
      <AutoTable
        baseUri='/dashboard/settings/system/job-logs/seasons'
        columns={jobLogsColumns}
        search={true}
        tableId='SeasonsJobLogs'
      />
    </div>
  )
}

export default SeasonsJobLogs
