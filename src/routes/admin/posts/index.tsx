import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import CreateOrUpdate from "@/Pages/Admin/Posts/CreateOrUpdate"
import DocView from "@/Pages/Admin/Posts/View/Index";
import categories from "./categories";

const relativeUri = 'posts/';

const index = [

    {
        path: '',
        children: categories,
    },
    {
        path: 'create',
        element: <AuthenticatedLayout uri={relativeUri + 'create'} permission="" Component={CreateOrUpdate} />,
    },
    {
        path: 'view/:id',
        element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} permission="" Component={DocView} />,
    },
    {
        path: 'view/:id/edit',
        element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} method="put" permission="" Component={CreateOrUpdate} />,
    },

]

export default index