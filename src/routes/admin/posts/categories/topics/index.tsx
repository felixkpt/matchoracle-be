import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Topics from "@/Pages/Admin/Posts/Categories/Topics/Index";

const relativeUri = 'admin/posts/categories/topics';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Topics} />,
    },
]

export default index