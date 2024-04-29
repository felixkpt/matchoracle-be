import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import Topics from "@/Pages/Admin/Posts/Categories/Topics/Index";

const relativeUri = 'admin/posts/categories/topics';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Topics} />,
    },
]

export default index