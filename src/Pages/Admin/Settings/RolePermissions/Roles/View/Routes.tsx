import useAxios from '@/hooks/useAxios';
import { useEffect, useState } from 'react'
import RoutesTree from '../../Tabs/Includes2/RoutesTree';
import { Route } from '@/interfaces';

const Routes = () => {

    const uri = 'admin/settings/role-permissions/permissions/routes'

    const [routes, setRoutes] = useState<Route[] | null>(null);
    const { data, get, loading } = useAxios();
    // const { data: savedData, fetchData: save } = useAxios(uri);


    useEffect(() => {
            get(uri);
    }, []);

    useEffect(() => {
        
        if (!loading && data) {
            setRoutes(data)
        }
        
    }, [data, loading])

    async function handleSubmit(checked: any) {

        const res = save({ checked })
        console.log(res)

    }

    return (
        <div>
            {routes && <RoutesTree routes={routes} handleSubmit={handleSubmit} />}
        </div>
    )
}

export default Routes