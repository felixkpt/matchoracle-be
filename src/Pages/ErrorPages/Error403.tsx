import { NavLink } from 'react-router-dom';
interface Props {
    previousUrl: string | null
    currentUrl: string
    setReloadKey: React.Dispatch<React.SetStateAction<number>>
}

const Error403 = ({ previousUrl, currentUrl, setReloadKey }: Props) => {

    return (
        <div className="position-absolute top-50 start-50 translate-middle w-100">
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-lg-6">
                        <div className="text-center mt-4">
                            <div className="contant_box_404 text-center">
                                <h1 className="display-1">403</h1>
                                <p className="lead">Forbidden</p>
                                <p>You do not have permission to access this page.</p>

                                {previousUrl &&
                                    <NavLink to={previousUrl} onClick={() => previousUrl === currentUrl && setReloadKey(curr => curr + 1)} className="link_404 rounded">{previousUrl === currentUrl ? 'Reload' : 'Go Back'}</NavLink>
                                }
                            </div>

                            <NavLink to="/">
                                Return to Dashboard
                            </NavLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Error403;
