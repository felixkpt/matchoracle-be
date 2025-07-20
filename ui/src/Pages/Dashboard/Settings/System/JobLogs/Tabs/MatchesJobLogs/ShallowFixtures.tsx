import AutoTable from '@/components/Autos/AutoTable'
import { jobLogsColumns } from '@/components/TableColumns'

const ShallowFixtures = () => {

    return (
        <div>
            <AutoTable
                baseUri='/dashboard/settings/system/job-logs/matches?task=recent_results'
                columns={jobLogsColumns}
                search={true}
                tableId='ShallowFixturesMatchesJoblogsTable'
            />
        </div>
    )
}

export default ShallowFixtures
