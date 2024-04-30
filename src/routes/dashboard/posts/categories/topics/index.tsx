import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import Topics from "../../../../../Pages/Dashboard/Posts/Categories/Topics/Index";

const relativeUri = 'dashboard/posts/categories/topics';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Topics} />,
    },
]

export default index