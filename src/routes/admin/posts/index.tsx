import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import CreateOrUpdate from "@/Pages/Admin/Posts/CreateOrUpdate"
import DocView from "@/Pages/Admin/Posts/View/Index";
import categories from "./categories";

const relativeUri = 'admin/posts/';

const index = [

    {
        path: '',
        children: categories,
    },
    {
        path: 'create',
        element: <DefaultLayout uri={relativeUri + 'create'} permission="" Component={CreateOrUpdate} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={DocView} />,
    },
    {
        path: 'view/:id/edit',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} method="put" permission="" Component={CreateOrUpdate} />,
    },

]

export default index