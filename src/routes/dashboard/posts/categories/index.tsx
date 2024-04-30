import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Docs from "../../../../Pages/Dashboard/Posts/Index"
import topics from "./topics";
import Categories from "../../../../Pages/Dashboard/Posts/Categories/Index";
import Category from "../../../../Pages/Dashboard/Posts/Categories/View/Index";

const relativeUri = 'dashboard/posts/categories/';

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