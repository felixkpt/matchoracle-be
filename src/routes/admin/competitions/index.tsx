import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Competitions from "@/Pages/Admin/Competitions/Index";
import Competition from "@/Pages/Admin/Competitions/View/Index";
import competitionAbbreviations from '@/routes/admin/competitions/competition-abbreviations'

const relativeUri = 'admin/competitions/';

const index = [

    {
        path: 'competition-abbreviations',
        children: competitionAbbreviations,
    },
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Competitions} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={Competition} />,
    },
]

export default index