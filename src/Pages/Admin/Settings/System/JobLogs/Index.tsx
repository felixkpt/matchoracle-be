import AutoTabs from "@/components/AutoTabs";
import SeasonsJobLogs from "./Tabs/SeasonsJobLogs";
import StandingsJobLogs from "./Tabs/StandingsJobLogs";
import MatchesJobLogs from "./Tabs/MatchesJobLogs";
import ResultsMatchJobLogs from "./Tabs/ResultsMatchJobLogs";
import FixturesMatchJobLogs from "./Tabs/FixturesMatchJobLogs";
import HistoricalResultsMatchJobLogs from "./Tabs/HistoricalResultsMatchJobLogs";
import PredictionsJobLogs from "./Tabs/PredictionsJobLogs";

type Props = {}

const Index = (props: Props) => {

    const tabs = [
        {
            name: "Seasons",
            content: <SeasonsJobLogs />,
        },
        {
            name: "Standings",
            content: <StandingsJobLogs />,
        },
        {
            name: "Matches",
            content: <MatchesJobLogs />,
        },
        {
            name: "Results Match",
            content: <ResultsMatchJobLogs />,
        },{
            name: "Fixtures Match",
            content: <FixturesMatchJobLogs />,
        },
        {
            name: "Historical Results Match",
            content: <HistoricalResultsMatchJobLogs />,
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