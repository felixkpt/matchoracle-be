import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Competitions from "@/Pages/Admin/Competitions/CompetitionAbbreviations/Index";

const relativeUri = 'admin/competitions/competition-abbreviations/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Competitions} />,
    }
]

export default index