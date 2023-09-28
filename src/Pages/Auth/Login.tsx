import React, { useEffect, useState } from 'react';
import { Link, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import useAxios from '@/hooks/useAxios';

export default function Login() {
    const { setUser, csrfToken, redirectTo } = useAuth();
    const navigate = useNavigate();

    // Initialize useAxios with the desired endpoint for login
    const { data, loading, post } = useAxios();

    // login user
    const handleSubmit = async (e: any) => {
        e.preventDefault();

        const { email, password } = e.target.elements;
        const body = {
            email: email.value,
            password: password.value,
        };

        csrfToken();
        await post('/login', body);

    }

    const [tried, setTried] = useState(false);

    useEffect(() => {
        if (tried === false && loading === true) setTried(true);

        if (loading === false && tried === true) {
            const user = data;

            if (user) {
                setUser(user);
                // Redirect the user
                navigate(redirectTo);

            }
        }
    }, [loading, tried]);

    return (

        <div className="col-lg-5">
            <div className="card shadow-lg border-0 rounded-lg mt-5">
                <div className="card-header"><h3 className="text-center font-weight-light my-4">Login</h3></div>
                <div className="card-body">
                    <form onSubmit={handleSubmit}>
                        <div className="form-floating mb-3">
                            <input className="form-control" id="inputEmail" type="email" name='email' placeholder="name@example.com" />
                            <label htmlFor="inputEmail">Email address</label>
                        </div>
                        <div className="form-floating mb-3">
                            <input className="form-control" id="inputPassword" type="password" name="password" placeholder="Password" />
                            <label htmlFor="inputPassword">Password</label>
                        </div>
                        <div className="form-check mb-3">
                            <input className="form-check-input" id="inputRememberPassword" type="checkbox" name="remember" value="" />
                            <label className="form-check-label" htmlFor="inputRememberPassword">Remember Password</label>
                        </div>
                        <div className="d-flex align-items-center justify-content-between mt-4 mb-0">
                            <NavLink className="small" to="/password">Forgot Password?</NavLink>
                            <button type="submit" className="btn btn-primary main-bg">Login</button>
                        </div>
                    </form>
                </div>
                <div className="card-footer text-center py-3">
                    <div className="small"><NavLink to="/register">Need an account? Sign up!</NavLink></div>
                </div>
            </div>
        </div>

    );
}
