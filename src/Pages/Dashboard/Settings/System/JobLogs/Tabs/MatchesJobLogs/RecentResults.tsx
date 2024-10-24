import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const RecentResults = () => {

    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/matches?task=recent_results'
                columns={jobLogsColumns}
                search={true}
                tableId='RecentResultsMatchesJoblogsTable'
            />
        </div>
    )
}

export default RecentResults
