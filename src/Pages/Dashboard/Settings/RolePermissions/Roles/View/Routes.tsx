import useAxios from '@/hooks/useAxios';
import { useEffect } from 'react'

const Routes = () => {

    const uri = 'dashboard/settings/role-permissions/permissions/routes'

    const { response, get, loading } = useAxios();


    useEffect(() => {
            get(uri);
    }, []);

    useEffect(() => {
        
        
    }, [response, loading])

    return (
        <div>
        </div>
    )
}

export default Routes