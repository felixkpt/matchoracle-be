import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Teams from "@/Pages/Admin/Teams/Index";
import Team from "@/Pages/Admin/Teams/View/Index";

const relativeUri = 'competitions/';

const index = [

    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Teams} />,
    },
    {
        path: 'club-teams',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Teams} />,
    },
    {
        path: 'national-teams',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Teams} />,
    },
    {
        path: 'view/:id',
        element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} permission="" Component={Team} />,
    },
]

export default index