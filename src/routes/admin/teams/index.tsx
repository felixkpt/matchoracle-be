import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Teams from "@/Pages/Admin/Teams/Index";
import Team from "@/Pages/Admin/Teams/View/Index";
import addresses from "@/routes/admin/teams/addresses";
import coaches from "@/routes/admin/teams/coaches";
import venues from "@/routes/admin/teams/venues";
import contracts from "@/routes/admin/teams/contracts";

const relativeUri = 'admin/competitions/';

const index = [

    {
        path: 'addresses',
        children: addresses,
    },
    {
        path: 'coaches',
        children: coaches,
    },
    {
        path: 'venues',
        children: venues,
    },
    {
        path: 'contracts',
        children: contracts,
    },
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