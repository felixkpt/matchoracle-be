import Loader from '@/components/Loader'
import { RoleInterface } from '@/interfaces/RolePermissionsInterfaces'

type Props = {
    currentRole: RoleInterface
    loading: boolean
    loaded: boolean
    reload: () => void
}

const MenuLoader = ({ currentRole, loading, loaded, reload }: Props) => {

    return (
        <div className='ps-2 pt-3'>
            {loading ?
                <Loader justify='start' />
                :
                <>
                    {
                        loaded ?
                            <div className='cursor-default'>
                                {
                                    !currentRole ?
                                        <>Could not load menu for <span className='text-decoration-underline cursor-pointer' title='Click to reload' onClick={() => reload()}>Guest</span></>
                                        : <>Could not load menu for <span className='text-decoration-underline cursor-pointer' title='Click to reload' onClick={() => reload()}>{currentRole.name}</span></>
                                }
                            </div>
                            :
                            <Loader justify='start' message='Please wait...' />

                    }
                </>
            }
        </div>
    )
}

export default MenuLoader