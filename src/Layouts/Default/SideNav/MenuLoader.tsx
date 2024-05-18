import Loader from '@/components/Loader'
import { UserInterface } from '@/interfaces/UserInterface'

type Props = {
    currentRole: RoleInterface
    loading: boolean
    user: UserInterface
}

const MenuLoader = ({ currentRole, loading, user }: Props) => {
    console.log(currentRole, loading, user)
    return (
        <div className='ps-2 pt-3'>
            {loading ?
                <Loader justify='start' />
                :
                <>
                    {
                        !currentRole ?
                            <>Could not load menu for <span className='text-decoration-underline'>Guest</span></>
                            : <>Could not load menu for <span className='text-decoration-underline'>{currentRole.name}</span></>
                    }
                </>
            }
        </div>
    )
}

export default MenuLoader