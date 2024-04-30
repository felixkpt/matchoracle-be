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
            content: <SeasonsJobLogs />,
        },
        {
            name: "Standings - Historical Results",
            content: <StandingsHistoricalResults />,
        },
        {
            name: "Standings - Recent Results",
            content: <StandingsRecentResults />,
        },
        {
            name: "Matches - Historical Results",
            content: <MatchesHistoricalResults />,
        },
        {
            name: "Matches - Recent Results",
            content: <MatchesRecentResults />,
        },
        {
            name: "Matches - Shallow Fixtures",
            content: <MatchesShallowFixtures />,
        },
        {
            name: "Matches - Fixures",
            content: <MatchesFixtures />,
        },

        {
            name: "Match - Historical Results",
            content: <MatchHistoricalResults />,
        },
        {
            name: "Match - Recent Results",
            content: <MatchRecentResults />,
        },
        {
            name: "Match - Shallow Fixtures",
            content: <MatchShallowFixtures />,
        },
        {
            name: "Match - Fixures",
            content: <MatchFixtures />,
        },
        {
            name: "Train Predictions",
            content: <TrainPredictionsJobLogs />,
        },
        {
            name: "Predictions",
            content: <PredictionsJobLogs />,
        },

    ];

    return (
        <div>
            <AutoTabs tabs={tabs} title="System job logs" />
        </div>
    )
}

export default Index