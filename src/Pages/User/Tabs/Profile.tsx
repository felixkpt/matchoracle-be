import React, { useEffect, useState } from 'react'
import defaultUserBackDrop from '@/assets/images/default_user_back_drop.png';
import useAutoPostDone from '@/hooks/autos/useAutoPostDone';
import BasicInfo from './Includes/BasicInfo';
import UpdatePass from './Includes/UpdatePass';
import { useAuth } from '@/contexts/AuthContext';

const Profile = () => {

	const { user, setUser } = useAuth()

	const [imageUrl, setImageUrl] = useState<string>('users/default-user.png')

	const { event } = useAutoPostDone()

	useEffect(() => {

		if (event && event?.id === 'profile-update') {
			const { data } = event
			if (data) {
				setUser(data)
			}
		}

	}, [event])

	useEffect(() => {

		if (user?.avatar) {
			setImageUrl(user.avatar)
		}

	}, [user])

	return (
		<div>
			<div className="mt-3">
				<div className="section-image">
					<div className="row">
						<div className="col-md-8 mb-4">
							<div className="card">
								<div className="card-body px-2">
									<BasicInfo user={user} imageUrl={imageUrl} setImageUrl={setImageUrl} />
									<hr className="my-4" />
									<UpdatePass />
								</div>
							</div>
						</div>

						<div className="col-md-4">
							<div className="card card-user">
								<div className="card-header no-padding">
									<div className="card-image">
										<img src={defaultUserBackDrop} alt="..." />
									</div>
								</div>
								<div className="card-body">
									<div className="author">
										<div className="avatar-wrapper position-relative author-image border-gray">
											<img className="profile-pic rounded-circle" src={imageUrl} alt="Profile pic" />
										</div>
										<h5 className="card-title">{user?.name}</h5>
										<p className="card-description">
											...
										</p>
									</div>
									<p className="card-description text-center">
										Hey there! this,
										<br /> is how your profile looks.
									</p>
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