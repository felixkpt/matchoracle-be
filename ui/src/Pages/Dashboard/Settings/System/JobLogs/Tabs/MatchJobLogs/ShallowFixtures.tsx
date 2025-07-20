import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const ShallowFixtures = () => {

    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/match?task=recent_results'
                columns={jobLogsColumns}
                search={true}
                tableId='ShallowFixturesMatchJoblogsTable'
            />
        </div>
    )
}

export default ShallowFixtures
