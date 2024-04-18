import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import ClubTeams from "@/Pages/Admin/Teams/ClubTeams";
import NationalTeams from "@/Pages/Admin/Teams/NationalTeams";
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
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={ClubTeams} />,
    },
    {
        path: 'club-teams',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={ClubTeams} />,
    },
    {
        path: 'national-teams',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={NationalTeams} />,
    },
    {
        path: 'view/:id',
        element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} permission="" Component={Team} />,
    },
]

export default index