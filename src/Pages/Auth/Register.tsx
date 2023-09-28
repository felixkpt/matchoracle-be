import React from 'react';
import { Link, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext.jsx';
import useAxios from '@/hooks/useAxios';

export default function Register() {
	const { setUser } = useAuth();
	const navigate = useNavigate();
	const { post } = useAxios()

	const handleSubmit = async (e: any) => {
		e.preventDefault();
		const { name, email, password, cpassword } = e.target.elements;

		const body = {
			name: name.value,
			email: email.value,
			password: password.value,
			password_confirmation: cpassword.value,
		}

		await post('/register', body).then((res) => {

			console.log(res)
			if (res) {
				setUser(res);
				return navigate("/profile");
			}
		})
	}

	return (
		<div className="col-lg-7">
			<div className="card shadow-lg border-0 rounded-lg mt-5">
				<div className="card-header"><h3 className="text-center font-weight-light my-4">Create Account</h3></div>
				<div className="card-body">
					<form onSubmit={handleSubmit}>
						<div className="form-floating mb-3">
							<input className="form-control" id="inputName" type="text" name="name" placeholder="John Doe" />
							<label htmlFor="inputName">Name</label>
						</div>
						<div className="form-floating mb-3">
							<input className="form-control" id="inputEmail" type="email" name="email" placeholder="johndoe@example.com" />
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
				</div>
				<div className="card-footer text-center py-3">
					<div className="small"><NavLink to="/login">Have an account? Go to login</NavLink></div>
				</div>
			</div>
		</div>
	);
}