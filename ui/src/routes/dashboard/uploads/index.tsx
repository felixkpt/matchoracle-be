import FileUploader from "@/Pages/Dashboard/Settings/Uploads/FileUploader";
import DefaultLayout from "../../../Layouts/Default/DefaultLayout";

const relativeUri = 'dashboard/uploads/';

const index = [
    {
        path: 'create',
        element: <DefaultLayout uri={relativeUri} permission="" Component={FileUploader} />,
    },
]

export default index