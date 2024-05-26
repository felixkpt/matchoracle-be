import AutoModal from '@/components/Autos/AutoModal';
import SimpleTable from '@/components/Autos/SimpleTable';
import { useAuth } from '@/contexts/AuthContext';
import useListSources from '../../../../../hooks/list-sources/useListSources';
import useAxios from '@/hooks/useAxios'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { UserInterface } from '@/interfaces/UserInterface';
import { config } from '@/utils/helpers';
import { Icon } from '@iconify/react/dist/iconify.js';
import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom';
import ChangePassword from './ChangePassword';
import useAutoPostDone from '../../../../../hooks/autos/useAutoPostDone';

const Index = () => {

  const { id } = useParams<{ id: string }>();
  const { setUser: setLoggedInUser, setVerified } = useAuth();

  const [key, setKey] = useState<number>(0)
  const [user, setUser] = useState<UserInterface>()

  const navigate = useNavigate();

  const { loading, errors, get } = useAxios()
  const { loading: loggingIn, errors: errorsLoggingIn, post: postLogin } = useAxios()

  const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

  const { event } = useAutoPostDone()

  useEffect(() => {
    getUser()
  }, [id, key])


  useEffect(() => {

    if (event && event.id == 'UpdateUserInfoForm') {
      setKey(key + 1)
    }
  }, [event])


  function getUser() {
    get('dashboard/settings/users/view/' + id).then((results) => {

      if (results.data) {
        const { data, ...others } = results.data
        setUser(data)
        setModelDetails(others)

      }

    })
  }

  async function loginUser() {

    if (user) {
      await postLogin(`dashboard/settings/users/view/auto-login/${user.id}`).then((results) => {
        const user = results.data;

        if (user) {
          setLoggedInUser(user);
          setVerified(false)
          // Redirect the user to the home page
          navigate(config.urls.home);
        }

      });

    }

  }

  function sendResetToken() {
    get(`dashboard/settings/users/view/token/${user.id}`).then(() => {
    })

  }

  const { rolePermissions: listSources } = useListSources()

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
                        <button type="button" className="btn btn-outline-info w-100 text-start" data-bs-toggle="modal" data-bs-target="#UpdateUserInfo">
                          <Icon fontSize={26} icon="streamline:interface-user-edit-actions-close-edit-geometric-human-pencil-person-single-up-user-write" />
                          <span className='ms-2'>Edit User</span>
                        </button>
                      </div>
                      <div className='col-12 col-lg-5 px-1'>
                        <button type="button" className="btn btn-outline-secondary w-100 text-start" onClick={sendResetToken}>
                          <Icon fontSize={26} icon={`ooui:edit-lock`} />
                          <span className='ms-2'>Send reset token</span>
                        </button>
                      </div>
                      <div className='col-12 col-lg-3 px-1'>
                        <button onClick={loginUser} className="btn btn-outline-dark w-100 text-start">
                          <Icon fontSize={26} icon={`uiw:login`} />
                          <span className='ms-2'>Login</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                : <div>Loading user info</div>
            }

            {
              user && <><AutoModal setKey={setKey} modelDetails={modelDetails} record={user} actionUrl={`/dashboard/settings/users/view/${user.id || 0}`} listSources={listSources} size='modal-lg' id='UpdateUserInfo' /></>
            }

          </div>
        </div>
      </div>
    </div>
  )
}

export default Index