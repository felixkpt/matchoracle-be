import AutoTabs from "@/components/Autos/AutoTabs";
import SeasonsJobLogs from "./Tabs/SeasonsJobLogs";
import PredictionsJobLogs from "./Tabs/PredictionsJobLogs";
import StandingsHistoricalResults from "./Tabs/StandingsJobLogs/HistoricalResults";
import StandingsRecentResults from "./Tabs/StandingsJobLogs/RecentResults";
import MatchesHistoricalResults from "./Tabs/MatchesJobLogs/HistoricalResults";
import MatchesRecentResults from "./Tabs/MatchesJobLogs/RecentResults";
import MatchesShallowFixtures from "./Tabs/MatchesJobLogs/ShallowFixtures";
import MatchesFixtures from "./Tabs/MatchesJobLogs/Fixtures";
import MatchHistoricalResults from "./Tabs/MatchJobLogs/HistoricalResults";
import MatchRecentResults from "./Tabs/MatchJobLogs/RecentResults";
import MatchShallowFixtures from "./Tabs/MatchJobLogs/ShallowFixtures";
import MatchFixtures from "./Tabs/MatchJobLogs/Fixtures";
import TrainPredictionsJobLogs from "./Tabs/TrainPredictionsJobLogs";

const Index = () => {

    const tabs = [
        {
            name: "Seasons",
            component: <SeasonsJobLogs />,
        },
        {
            name: "Standings - Historical Results",
            component: <StandingsHistoricalResults />,
        },
        {
            name: "Standings - Recent Results",
            component: <StandingsRecentResults />,
        },
        {
            name: "Matches - Historical Results",
            component: <MatchesHistoricalResults />,
        },
        {
            name: "Matches - Recent Results",
            component: <MatchesRecentResults />,
        },
        {
            name: "Matches - Shallow Fixtures",
            component: <MatchesShallowFixtures />,
        },
        {
            name: "Matches - Fixures",
            component: <MatchesFixtures />,
        },

        {
            name: "Match - Historical Results",
            component: <MatchHistoricalResults />,
        },
        {
            name: "Match - Recent Results",
            component: <MatchRecentResults />,
        },
        {
            name: "Match - Shallow Fixtures",
            component: <MatchShallowFixtures />,
        },
        {
            name: "Match - Fixures",
            component: <MatchFixtures />,
        },
        {
            name: "Train Predictions",
            component: <TrainPredictionsJobLogs />,
        },
        {
            name: "Predictions",
            component: <PredictionsJobLogs />,
        },

    ];

    return (
        <div>
            <AutoTabs tabs={tabs} title="System job logs" />
        </div>
    )
}

export default Index