import useAxios from '@/hooks/useAxios';
import { useEffect, useState } from 'react'
import RoutesTree from '../../Tabs/Includes2/RoutesTree';
import { Route } from '@/interfaces';

const Routes = () => {

    const uri = 'dashboard/settings/role-permissions/permissions/routes'

    const [routes, setRoutes] = useState<Route[] | null>(null);
    const { results, get, loading } = useAxios();


    useEffect(() => {
            get(uri);
    }, []);

    useEffect(() => {
        
        if (!loading && results.data) {
            setRoutes(results.data)
        }
        
    }, [results, loading])

    async function handleSubmit() {

    }

    return (
        <div>
            {routes && <RoutesTree routes={routes} handleSubmit={handleSubmit} />}
        </div>
    )
}

export default Routes