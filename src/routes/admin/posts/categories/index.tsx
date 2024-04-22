import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Docs from "@/Pages/Admin/Posts/Index"
import topics from "./topics";
import Categories from "@/Pages/Admin/Posts/Categories/Index";
import Category from "@/Pages/Admin/Posts/Categories/View/Index";

const relativeUri = 'admin/posts/categories/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Docs} />,
    },
    
    {
        path: 'categories',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Categories} />,
    },
    {
        path: ':slug',
        element: <DefaultLayout uri={relativeUri + ':slug'} permission="" Component={Category} />,
    },
    {
        path: ':slug/topics',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Categories} />,
        children: topics,
    }
]

export default index