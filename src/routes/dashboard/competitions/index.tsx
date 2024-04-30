import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Competitions from "../../../Pages/Dashboard/Competitions/Index";
import Competition from "../../../Pages/Dashboard/Competitions/View/Index";
import competitionAbbreviations from '@/routes/dashboard/competitions/competition-abbreviations'

const relativeUri = 'dashboard/competitions/';

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