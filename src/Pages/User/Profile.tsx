import { useAuth } from '@/contexts/AuthContext'
import useAxios from '@/hooks/useAxios'
import { emitAjaxPost } from '@/utils/helpers'
import { Icon } from '@iconify/react/dist/iconify.js'
import { useEffect, useState } from 'react'
import defaultUserBackDrop from '@/assets/images/default_user_back_drop.png';
import { subscribe, unsubscribe } from '@/utils/events'

const Profile = () => {

	const { user, setUser } = useAuth()

	const { getFile: getImage } = useAxios();

	const [imageUrl, setImageUrl] = useState('users/default-user.png')

	useEffect(() => {

		const handleAjaxPostDone = (event: Event) => {
			if (event?.detail) {
				const { elementId, results } = event.detail

				if (elementId === 'profile-update' && results) {
					setUser(results)
				}
			}
		}

		subscribe('ajaxPostDone', handleAjaxPostDone)

		return () => unsubscribe('ajaxPostDone', handleAjaxPostDone)
	}, [])

	useEffect(() => {

		if (user) {
			handleFetchImage(user.avatar || imageUrl)
		}

	}, [user])

	const handleFetchImage = async (src: string) => {
		try {
			const imageBlob = await getImage(src);
			const imageSrc = URL.createObjectURL(imageBlob);
			setImageUrl(imageSrc)

		} catch (error) {
			console.error('Error fetching image:', error);
		}
	};

	const handleImageChange = (event) => {
		const file = event.target.files[0]; // Get the selected file
		if (file) {
			const imageSrc = URL.createObjectURL(file);
			setImageUrl(imageSrc);
		}
	};

	return (
		<div>
			<div className="container-xxl">
				<div className="section-image">
					<div className="row">
						<div className="card col-md-8 mb-4">
							<div className="card-header">
								<div className="row align-items-center">
									<div className="col-md-8">
										<h3 className="mb-0">Edit Profile</h3>
									</div>
								</div>
							</div>
							<div className="card-body">
								{
									user ?
										<form id='profile-update' method='post' action-url={`admin/users/user/profile`} onSubmit={(e: any) => emitAjaxPost(e)} encType='multipart/form-data' className="flex justify-center">
											<input type="hidden" name="_method" value="patch" />
											<h6 className="heading-small text-muted mb-4">User information</h6>
											<div className="pl-lg-4">
												<div className="form-floating mb-3">
													<input type="text" name="name" id="input-name" className="form-control" placeholder="Name" defaultValue={`${user.name}`} autoFocus />
													<label className="form-label" htmlFor="input-name">
														<i className="w3-xxlarge fa fa-user"></i>Name
													</label>
												</div>
												<div className="form-floating mb-3">
													<input type="email" name="email" id="input-email" className="form-control" placeholder="Email" defaultValue={user.email} />
													<label className="form-label" htmlFor="input-email"><i className="w3-xxlarge fa fa-envelope-o"></i>Email</label>
												</div>
												<div className="form-floating mb-3">
													<div>
														<div className="avatar-wrapper position-relative">
															<img className="profile-pic rounded-circle" src={imageUrl} alt="Profile pic" />
															<div className="upload-button" onClick={() => document.getElementById("avatarUpload")?.click()}>
																<Icon className='position-absolute top-50 start-50 translate-middle arrow-circle-up' icon={`mdi:arrow-up`} />
															</div>
															<input
																id="avatarUpload"
																type="file"
																name="avatar"
																onChange={handleImageChange}
																accept="image/*" />
														</div>
													</div>
												</div>
												<div className="text-center">
													<button type="submit" className="btn btn-warning mt-4">Save</button>
												</div>
											</div>
										</form>
										:
										<div>Loading...</div>
								}

								<hr className="my-4" />

								<form method='post' action-url={`admin/users/user/update-self-password`} onSubmit={(e: any) => emitAjaxPost(e)} className="flex justify-center">
									<input type="hidden" name="_method" value="patch" />
									<h6 className="heading-small text-muted mb-4">Password</h6>
									<div className="pl-lg-4">
										<div className="form-floating mb-3">
											<input type="password" name="current_password" id="input-current-password" className="form-control" placeholder="Current Password" defaultValue="" />
											<label className="form-label" htmlFor="input-current-password">
												<i className="w3-xxlarge fa fa-eye-slash"></i>Current Password
											</label>
										</div>
										<div className="form-floating mb-3">
											<input type="password" name="password" id="input-password" className="form-control" placeholder="New Password" defaultValue="" />
											<label className="form-label" htmlFor="input-password">
												<i className="w3-xxlarge fa fa-eye-slash"></i>New Password
											</label>
										</div>
										<div className="form-floating mb-3">
											<input type="password" name="password_confirmation" id="input-password-confirmation" className="form-control" placeholder="Confirm New Password" defaultValue="" />
											<label className="form-label" htmlFor="input-password-confirmation">
												<i className="w3-xxlarge fa fa-eye-slash"></i>Confirm New Password
											</label>
										</div>
										<div className="text-center">
											<button type="submit" className="btn btn-warning mt-4">Change password</button>
										</div>
									</div>
								</form>
							</div>
						</div>
						<div className="col-md-4">
							<div className="card card-user">
								<div className="card-header no-padding">
									<div className="card-image">
										<img src={defaultUserBackDrop} alt="..." />
									</div>
								</div>
								<div className="card-body ">
									<div className="author">
										<a href="#">
											<img className="avatar border-gray" src={imageUrl} alt="..." />
											<h5 className="card-title">Admin</h5>
										</a>
										<p className="card-description">
											...
										</p>
									</div>
									<p className="card-description text-center">
										Hey there! As you can see,
										<br /> it is already looking great.
									</p>
								</div>
								<div className="card-footer ">
									<hr />
									<div className="button-container text-center">
										<button href="#" className="btn btn-simple btn-link btn-icon">
											<i className="fa fa-facebook-square"></i>
										</button>
										<button href="#" className="btn btn-simple btn-link btn-icon">
											<i className="fa fa-twitter"></i>
										</button>
										<button href="#" className="btn btn-simple btn-link btn-icon">
											<i className="fa fa-google-plus-square"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	)
}

export default Profile