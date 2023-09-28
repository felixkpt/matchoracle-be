import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Docs from "@/Pages/Admin/Posts/Index"
import topics from "./topics";
import Categories from "@/Pages/Admin/Posts/Categories/Index";
import Category from "@/Pages/Admin/Posts/Categories/View/Index";

const relativeUri = 'posts/categories/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Docs} />,
    },
    
    {
        path: 'categories',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Categories} />,
    },
    {
        path: ':slug',
        element: <AuthenticatedLayout uri={relativeUri + ':slug'} permission="" Component={Category} />,
    },
    {
        path: ':slug/topics',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Categories} />,
        children: topics,
    }
]

export default index