import SubmitButton from '@/components/SubmitButton'
import { UserInterface } from '@/interfaces/UncategorizedInterfaces'
import { publish } from '@/utils/events'
import { Icon } from '@iconify/react/dist/iconify.js'

interface Props {
    user: UserInterface
    imageUrl: string
    setImageUrl: React.Dispatch<React.SetStateAction<string>>
}

const BasicInfo = ({ user, imageUrl, setImageUrl }: Props) => {

    const handleImageChange = (event) => {
        const file = event.target.files[0]; // Get the selected file
        if (file) {
            const imageSrc = URL.createObjectURL(file);
            setImageUrl(imageSrc);
        }
    };

    return (
        <div className="accordion" id="userAccordionPanels">
            <div className="accordion-item">
                <h2 className="accordion-header">
                    <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target={`#userCollapseBasicInfo`} aria-expanded={`true`} aria-controls={`userCollapseBasicInfo`} aria-labelledby={`headingBasicInfo`}>
                        Basic info
                    </button>
                </h2>
                <div id={`userCollapseBasicInfo`} className={`accordion-collapse collapse show`}>
                    <div className="accordion-body">
                        <div>
                            {
                                user ?
                                    <form id='profile-update' method='post' data-action={`/auth/profile`} onSubmit={(e) => publish('autoPost', e)} encType='multipart/form-data' className="flex justify-center">
                                        <input type="hidden" name="_method" value="patch" />
                                        <div className="pl-lg-4">
                                            <div className="form-floating mb-3">
                                                <input type="text" name="name" id="input-name" className="form-control" placeholder="Name" defaultValue={`${user.name}`} />
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
                                                    <div className="avatar-wrapper position-relative" title='Click to upload new piture' onClick={() => document.getElementById("avatarUpload")?.click()}>
                                                        <img className="profile-pic rounded-circle" src={imageUrl} alt="Profile pic" />
                                                        <div className="upload-button p-1">
                                                            <div className="position-absolute top-50 start-50 translate-middle arrow-circle-up">
                                                                <Icon icon={`simple-line-icons:info`} />
                                                            </div>
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
                                                <SubmitButton className="btn btn-warning mt-4">Save</SubmitButton>
                                            </div>
                                        </div>
                                    </form>
                                    :
                                    <div>Loading...</div>
                            }
                        </div>
                    </div>
                </div>

            </div>
        </div>
    )
}

export default BasicInfo