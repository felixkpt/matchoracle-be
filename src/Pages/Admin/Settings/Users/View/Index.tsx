import AutoModal from '@/components/AutoModal';
import SimpleTable from '@/components/SimpleTable';
import { useAuth } from '@/contexts/AuthContext';
import useListSources from '@/hooks/apis/useListSources';
import useAxios from '@/hooks/useAxios'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { UserInterface } from '@/interfaces/UserInterface';
import { publish } from '@/utils/events';
import { Icon } from '@iconify/react/dist/iconify.js';
import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom';

type Props = {}

const Index = (props: Props) => {

  const { id } = useParams<{ id: string }>();
  const { setUser: setLoggedInUser, setVerified } = useAuth();

  const [key, setKey] = useState<number>(0)
  const [user, setUser] = useState<UserInterface>()

  const navigate = useNavigate();

  const { data: userData, loading, errors, get } = useAxios()
  const { data: dataLoggedIn, loading: loggingIn, errors: errorsLoggingIn, post: postLogin } = useAxios()

  const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

  useEffect(() => {
    get('admin/settings/users/view/' + id)
  }, [id, key])

  useEffect(() => {
    if (!loading && userData) {
      const { data, ...others } = userData
      setUser(userData.data)
      setModelDetails(others)
    }

  }, [user, loading])

  async function loginUser() {

    if (user) {
      await postLogin(`admin/settings/users/view/login/${user.id}`);

    }

  }

  useEffect(() => {

    if (loggingIn === false && dataLoggedIn) {
      const user = dataLoggedIn;

      if (user) {

        setLoggedInUser(user);
        setVerified(false)

        // Redirect the user to the home page
        navigate('/admin');

      }
    }

  }, [dataLoggedIn, loggingIn]);

  const { rolePermissions: list_sources } = useListSources()

  return (
    <div className="row">
      <div className="col-md-12">
        <div className="card">
          <div className="card-body">
            {
              user ?
                <div className="row">
                  <div className="col-md-6">
                    <div className="model-view">
                      {
                        modelDetails &&
                        <SimpleTable htmls={modelDetails.htmls} exclude={['created_at']} record={user} />
                      }
                    </div>
                  </div>
                  <div className="col-md-6">
                    <h3>Main Actions</h3>

                    <div className='row gap-2 gap-lg-0'>
                      <div className='col-12 col-lg-4 px-1'>
                        <button type="button" className="btn btn-info text-white w-100 text-start" data-bs-toggle="modal" data-bs-target="#UpdateUserInfo">
                          <Icon fontSize={26} icon="streamline:interface-user-edit-actions-close-edit-geometric-human-pencil-person-single-up-user-write" />
                          <span className='ms-2'>Edit User</span>
                        </button>
                      </div>
                      <div className='col-12 col-lg-5 px-1'>
                        <button type="button" className="btn btn-info text-white w-100 text-start" data-bs-toggle="modal" data-bs-target="#update_password">
                          <Icon fontSize={26} icon={`ooui:edit-lock`} />
                          <span className='ms-2'>Change Password</span>
                        </button>
                      </div>
                      <div className='col-12 col-lg-3 px-1'>
                        <button onClick={loginUser} className="btn btn-outline-primary w-100 text-start">
                          <Icon fontSize={26} icon={`uiw:login`} />
                          <span className='ms-2'>Login</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div className={`modal fade`} id="update_password" data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden={`true`}>

                    <div className="modal-dialog modal-dialog-top animated zoomIn animated-3x   ">
                      <div className="modal-content">
                        <div className="modal-header">
                          <h5 className="modal-title title" id="update_password_label">New Password</h5>
                          <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div className="modal-body">
                          <div className="section">
                            <form encType="" method="post" action-url={'/admin/settings/users/view/update-others-password'} onSubmit={(e: any) => publish('ajaxPost', e)} >
                              <input type="hidden" name="user_id" value={id} />
                              <input type="hidden" name="_method" value="patch" />
                              <div className="form-group password">
                                <label className="form-label label_password">Password</label>
                                <input type="password" name="password" className="form-control" />
                              </div>
                              <div className="form-group password_confirmation">
                                <label className="form-label label_password_confirmation">Password Confirmation</label>
                                <input type="password" name="password_confirmation" className="form-control" />
                              </div>
                              <div className="form-group mt-2">
                                <button type="submit" className="btn  btn-primary submit-btn ">Save Information</button>
                              </div>
                            </form>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>
                : <div>Loading user info</div>
            }

            {
              user && <><AutoModal setKey={setKey} modelDetails={modelDetails} record={user} actionUrl={`/admin/settings/users/view/${user.id || 0}`} list_sources={list_sources} size='modal-lg' id='UpdateUserInfo' /></>
            }

          </div>
        </div>
      </div>
    </div>
  )
}

export default Index