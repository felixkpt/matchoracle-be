import { publish } from '@/utils/events';

type Props = {
    id: string | number
}

const ChangePassword = ({ id }: Props) => {
    return (
        <div className={`modal fade`} id="update_password" data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden={`true`}>

            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title title" id="update_password_label">Change Password</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div className="modal-body">
                        <div className="section">
                            <form method="post" data-action={'/dashboard/settings/users/view/update-others-password'} onSubmit={(e: any) => publish('autoPost', e)} >
                                <input type="hidden" name="user_id" value={id} />
                                <input type="hidden" name="_method" value="patch" />
                                <div className="form-group password">
                                    <label className="form-label label_password">New Password</label>
                                    <input type="password" name="password" className="form-control" />
                                </div>
                                <div className="form-group password_confirmation">
                                    <label className="form-label label_password_confirmation">Password Confirmation</label>
                                    <input type="password" name="password_confirmation" className="form-control" />
                                </div>
                                <div className="form-group mt-2">
                                    <button type="submit" className="btn btn-success submit-btn">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default ChangePassword