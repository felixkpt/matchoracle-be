import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const RecentResults = () => {

    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/match?task=recent_results'
                columns={jobLogsColumns}
                search={true}
                tableId='RecentResultsMatchJoblogsTable'
            />
        </div>
    )
}

export default RecentResults
