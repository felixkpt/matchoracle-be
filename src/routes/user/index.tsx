import DefaultLayout from "../../Layouts/Default/DefaultLayout";
import Profile from "@/Pages/User/Profile";

const relativeUri = 'users/user/'

const index = [

    {
        path: 'profile',
        element: <DefaultLayout uri={relativeUri + 'profile'} permission="" Component={Profile} />,
    }

]

export default index