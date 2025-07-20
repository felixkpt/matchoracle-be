import { useNavigate } from 'react-router-dom';
import useAxios from '@/hooks/useAxios';
import { NavLink } from 'react-router-dom';
import SubmitButton from '../../components/SubmitButton';

const Password = () => {
    const navigate = useNavigate();
    const { post, loading } = useAxios(); // Include loading from useAxios

    const handleSubmit = async (e: any) => {
        e.preventDefault();
        const { email } = e.target.elements;

        const body = {
            email: email.value,
        };

        await post('/auth/password', body).then((res) => {
            // If 'sent' is true, redirect the user to the confirmation page
            if (res) {
                navigate('/reset-password-confirm');
            }
        });
    };

    return (
        <div className="col-lg-5">
            <div className="card shadow-lg border-0 rounded-lg mt-5">
                <div className="card-header">
                    <h3 className="text-center font-weight-light my-4">Password Recovery</h3>
                </div>
                <div className="card-body">
                    <div className="small mb-3 text-muted">
                        Enter your email address and we will send you a link to reset your password.
                    </div>
                    <form onSubmit={handleSubmit}>
                        <div className="form-floating mb-3">
                            <input
                                className="form-control"
                                id="inputEmail"
                                type="email"
                                name="email"
                                placeholder="name@example.com"
                            />
                            <label htmlFor="inputEmail">Email address</label>
                        </div>
                        <div className="d-flex align-items-center justify-content-between mt-4 mb-0">
                            <NavLink className="small" to="/login">
                                Return to login
                            </NavLink>
                            <SubmitButton loading={loading}>
                                Reset Password
                            </SubmitButton>
                        </div>
                    </form>
                </div>
                <div className="card-footer text-center py-3">
                    <div className="small">
                        <NavLink to="/register">Need an account? Sign up!</NavLink>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Password;
