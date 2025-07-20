import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Competitions from "../../../Pages/Dashboard/Competitions/Index";
import Competition from "../../../Pages/Dashboard/Competitions/View/Index";
import competitionAbbreviations from '@/routes/dashboard/competitions/competition-abbreviations'
import predictionLogs from '@/routes/dashboard/competitions/prediction-logs'

const relativeUri = 'dashboard/competitions/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Competitions} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={Competition} />,
    },
    {
        path: 'competition-abbreviations',
        children: competitionAbbreviations,
    },
    {
        path: 'prediction-logs',
        children: predictionLogs,
    },
]

export default index