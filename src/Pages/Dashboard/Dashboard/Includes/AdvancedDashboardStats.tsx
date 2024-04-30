import StatsAndPerformance from "./StatsAndPerformance/Index";
import UsersReport from "./UsersReport/Index";

type Props = {}

const AdvancedDashboardStats = (props: Props) => {

    return (
        <div>
            <UsersReport />
            <StatsAndPerformance />
        </div>
    )
}

export default AdvancedDashboardStats