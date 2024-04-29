import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Competitions from "@/Pages/Admin/Competitions/CompetitionAbbreviations/Index";

const relativeUri = 'admin/competitions/competition-abbreviations/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Competitions} />,
    }
]

export default index