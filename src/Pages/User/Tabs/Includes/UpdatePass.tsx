import SubmitButton from "@/components/SubmitButton"
import { publish } from "@/utils/events"

const UpdatePass = () => {

    return (
        <div className="accordion" id="userAccordionPanels">
            <div className="accordion-item mt-3">
                <h2 className="accordion-header">
                    <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target={`#userCollapsePass`} aria-expanded={`false`} aria-controls={`userCollapsePass`} aria-labelledby={`headingPass`} >
                        Password Update
                    </button>
                </h2>
                <div id={`userCollapsePass`} className={`accordion-collapse collapse`}>
                    <div className="accordion-body">
                        <form method='post' data-action={`/auth/update-password`} onSubmit={(e) => publish('autoPost', e)} className="flex justify-center">
                            <input type="hidden" name="_method" value="patch" />
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
                                    <SubmitButton className="btn btn-warning mt-4">Change password</SubmitButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default UpdatePass