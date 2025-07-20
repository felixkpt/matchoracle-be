import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import CompetitionAbbreviations from "../../../../Pages/Dashboard/Competitions/CompetitionAbbreviations/Index";

const relativeUri = 'dashboard/competitions/competition-abbreviations/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={CompetitionAbbreviations} />,
    }
]

export default index