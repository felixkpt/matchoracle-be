import { useAuth } from '@/contexts/AuthContext';
import useAxios from '@/hooks/useAxios';
import { useEffect, useState } from 'react';
import { NavLink, useNavigate, useParams } from 'react-router-dom'

type Props = {}

const PasswordSet = (props: Props) => {
    const { setUser } = useAuth();

    const navigate = useNavigate();
    const { loading: loadingPost, post } = useAxios()
    const { loading: loadingGet, get: getEmail } = useAxios()
    const { token } = useParams()

    const [email, setEmail] = useState<string>()

    useEffect(() => {

        if (token)
            fetchEmail(token)

    }, [token])

    async function fetchEmail(token: string) {
        await getEmail('password/' + token).then((res) => {
            if (res)
                setEmail(res.email)
        })
    }

    const handleSubmit = async (e: any) => {
        e.preventDefault();
        const { password, cpassword } = e.target.elements;

        const body = {
            token,
            email,
            password: password.value,
            password_confirmation: cpassword.value,
        }

        await post('/password-set', body).then((res) => {

            if (res) {
                setUser(res);
                // Redirect the user to the home page
                navigate('/admin');
            }
        })
    }

    return (
        <div className="col-lg-7">
            <div className="card shadow-lg border-0 rounded-lg mt-5">
                <div className="card-header"><h3 className="text-center font-weight-light my-4">Set new password</h3></div>
                <div className="card-body">
                    {
                        email ?
                            <form onSubmit={handleSubmit}>
                                <div className="form-floating mb-3">
                                    <input className="form-control" id="inputEmail" type="email" readOnly placeholder="johndoe@example.com" defaultValue={email} />
                                    <label htmlFor="inputEmail">Email address</label>
                                </div>
                                <div className="row mb-3">
                                    <div className="col-md-6">
                                        <div className="form-floating mb-3 mb-md-0">
                                            <input className="form-control" id="inputPassword" type="password" name="password" placeholder="Create a password" />
                                            <label htmlFor="inputPassword">Password</label>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-floating mb-3 mb-md-0">
                                            <input className="form-control" id="inputPasswordConfirm" type="password" name="cpassword" placeholder="Confirm password" />
                                            <label htmlFor="inputPasswordConfirm">Confirm Password</label>
                                        </div>
                                    </div>
                                </div>
                                <div className="mt-4 mb-0">
                                    <div className="d-grid"><button className="btn btn-primary btn-block">Create Account</button></div>
                                </div>
                            </form>
                            :
                            <div>
                                {
                                    loadingGet ?
                                        <div className="d-flex align-items-center gap-3">
                                            <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                        </div>
                                        :
                                        <div className='alert alert-danger'>Error while retrieving your information</div>
                                }
                            </div>

                    }

                </div>
                <div className="card-footer text-center py-3">
                    <div className="small"><NavLink to="/login">Have an account? Go to login</NavLink></div>
                </div>
            </div>
        </div>
    )
}

export default PasswordSet