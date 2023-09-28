import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Profile from "@/Pages/User/Profile";

const relativeUri = 'users/user/'

const index = [

    {
        path: 'profile',
        element: <AuthenticatedLayout uri={relativeUri + 'profile'} permission="" Component={Profile} />,
    }

]

export default index