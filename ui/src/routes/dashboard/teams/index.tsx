import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import ClubTeams from "../../../Pages/Dashboard/Teams/ClubTeams";
import NationalTeams from "../../../Pages/Dashboard/Teams/NationalTeams";
import Team from "../../../Pages/Dashboard/Teams/View/Index";
import addresses from "@/routes/dashboard/teams/addresses";
import coaches from "@/routes/dashboard/teams/coaches";
import venues from "@/routes/dashboard/teams/venues";

const relativeUri = 'dashboard/competitions/';

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
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={ClubTeams} />,
    },
    {
        path: 'club-teams',
        element: <DefaultLayout uri={relativeUri} permission="" Component={ClubTeams} />,
    },
    {
        path: 'national-teams',
        element: <DefaultLayout uri={relativeUri} permission="" Component={NationalTeams} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={Team} />,
    },
]

export default index