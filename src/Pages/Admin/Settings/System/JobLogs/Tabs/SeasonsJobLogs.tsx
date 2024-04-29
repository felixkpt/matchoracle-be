import AutoTable from '@/components/AutoTable'

type Props = {}

const SeasonsJobLogs = (props: Props) => {

  const columns = [
    { key: 'id' },
    { key: 'date' },
    { key: 'job_run_counts', label: 'Job Runs', },
    { key: 'competition_run_counts', label: 'Competition runs' },
    { key: 'fetch_run_counts', label: 'Fetch runs' },
    { key: 'fetch_success_counts', label: 'Fetch success' },
    { key: 'fetch_failed_counts', label: 'Fetch failed' },
    { key: 'updated_seasons_counts', label: 'Updated seasons' },
    { label: 'Last run', key: 'Last_run' },
    { label: 'Created At', key: 'Created_at' },
  ]

  return (
    <div>
      <AutoTable
        baseUri='/admin/settings/system/job-logs/seasons'
        columns={columns}
        search={true}
        tableId='SeasonsJobLogs'
      />
    </div>
  )
}

export default SeasonsJobLogs