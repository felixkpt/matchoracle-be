import { useEffect, useState } from 'react'
import { NavLink } from 'react-router-dom'

interface Props {
  previousUrl?: string | null
  currentUrl?: string
  setReloadKey?: React.Dispatch<React.SetStateAction<number>>
  timeout?: number
}

const Error404 = ({ previousUrl, currentUrl, setReloadKey, timeout }: Props) => {

  const [show, setShow] = useState(false)

  const millisecs = (timeout && timeout > 0 ? timeout : 1) * 1000
  useEffect(() => {
    setTimeout(() => {
      setShow(true)
    }, millisecs);

  }, [timeout])

  return (
    <>
      {
        show ?
          <div className="d-flex align-items-center h-100vh">
            <div className="w-100 row justify-content-center">
              <div className="col-lg-6">
                <div className="text-center mt-4">
                  <div className="contant_box_404 text-center">
                    <div className="four_zero_four_bg rounded mb-2">
                      <h1 className="display-1">404</h1>
                    </div>

                    <p className="lead">Looks like you're lost</p>

                    <p>the page you are looking for is not avaible!</p>

                    {previousUrl &&
                      <NavLink to={previousUrl} onClick={() => previousUrl === currentUrl && setReloadKey(curr => curr + 1)} className="link_404 rounded">{previousUrl === currentUrl ? 'Reload' : 'Go Back'}</NavLink>
                    }
                  </div>
                  <NavLink to="/">
                    Return to Dashboard
                  </NavLink>
                </div>
              </div>
            </div>
          </div>
          : null
      }</>


  )
}

export default Error404