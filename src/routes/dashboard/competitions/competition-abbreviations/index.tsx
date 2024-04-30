import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Competitions from "../../../../Pages/Dashboard/Competitions/CompetitionAbbreviations/Index";

const relativeUri = 'dashboard/competitions/competition-abbreviations/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Competitions} />,
    }
]

export default index