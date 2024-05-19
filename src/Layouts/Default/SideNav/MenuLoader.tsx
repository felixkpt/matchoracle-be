import Loader from '@/components/Loader'
import { RoleInterface } from '@/interfaces/RolePermissionsInterfaces'

type Props = {
    currentRole: RoleInterface
    loading: boolean
    refreshedRoutePermissions: boolean
}

const MenuLoader = ({ currentRole, loading, refreshedRoutePermissions }: Props) => {

    return (
        <div className='ps-2 pt-3'>
            {loading ?
                <Loader justify='start' />
                :
                <>
                    {
                        refreshedRoutePermissions ?
                            <>
                                {
                                    !currentRole ?
                                        <>Could not load menu for <span className='text-decoration-underline'>Guest</span></>
                                        : <>Could not load menu for <span className='text-decoration-underline'>{currentRole.name}</span></>
                                }
                            </>
                            : null
                    }
                </>
            }
        </div>
    )
}

export default MenuLoader