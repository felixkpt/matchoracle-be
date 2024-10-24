import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const Fixtures = () => {

    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/match?task=recent_results'
                columns={jobLogsColumns}
                search={true}
                tableId='FixturesMatchJoblogsTable'
                />
        </div>
    )
}

export default Fixtures
