import { useEffect, useState } from "react";
import { DashboardStatsInterface } from "@/interfaces/FootballInterface";
import useAxios from "@/hooks/useAxios";
import StatsAndPerformance from "./StatsAndPerformance/Index";
import UsersReport from "./UsersReport/Index";
import SystemAutomationReport from "./SystemAutomationReport/Index";

type Props = {}

const AdvancedDashboardStats = (props: Props) => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`admin/advanced-stats`).then((results: any) => {
            if (results) {
                setStats(results)
            }
        })
    }

    return (
        <div>
            <UsersReport stats={stats} />
            <SystemAutomationReport stats={stats} />
            <StatsAndPerformance stats={stats} />
        </div>
    )
}

export default AdvancedDashboardStats