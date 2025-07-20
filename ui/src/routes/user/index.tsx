import DefaultLayout from "../../Layouts/Default/DefaultLayout";
import Index from "@/Pages/User/Index";

const relativeUri = 'user/'

const index = [

    {
        path: 'account',
        element: <DefaultLayout uri={relativeUri + 'account'} permission="" Component={Index} />,
    }

]

export default index