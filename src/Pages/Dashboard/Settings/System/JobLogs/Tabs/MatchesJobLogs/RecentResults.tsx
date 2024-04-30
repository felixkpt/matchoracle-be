import AutoTable from '@/components/Autos/AutoTable'

type Props = {}

const RecentResults = (props: Props) => {

    const columns = [
        { key: 'id' },
        { key: 'date' },
        { key: 'job_run_counts', label: 'Job Runs', },
        { key: 'competition_run_counts', label: 'Competition runs' },
        { key: 'fetch_run_counts', label: 'Fetch runs' },
        { key: 'fetch_success_counts', label: 'Fetch success' },
        { key: 'fetch_failed_counts', label: 'Fetch failed' },
        { key: 'updated_matches_counts', label: 'Updated matches' },
        { label: 'Last run', key: 'Last_run' },
        { label: 'Created At', key: 'Created_at' },
    ]


    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/matches?task=recent_results'
                columns={columns}
                search={true}
                tableId='RecentResultsMatchesJoblogsTable'
            />
        </div>
    )
}

export default RecentResults
