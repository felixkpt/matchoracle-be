import usePermissions from '@/hooks/usePermissions';
import { PageHeaderInterface } from '@/interfaces/UncategorizedInterfaces';
import { Icon } from '@iconify/react/dist/iconify.js';
import { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';


const PageHeader = ({ title, action, actionText, actionLink, permission, method = 'post', actionTargetId, listUrl, setRecord }: PageHeaderInterface) => {
  const [textPermission, setTextPermission] = useState(permission)
  const { userCan } = usePermissions()
  const isButton = action === 'button';

  const renderAction = () => {
    if (isButton) {
      return (
        <button type="button" className="btn btn-primary text-white" data-bs-toggle="modal" id={`${actionTargetId || 'ActionButton'}Trigger`} data-bs-target={`#${actionTargetId || 'ActionButton'}`} onClick={(e) => setRecord && e.isTrusted && setRecord(undefined)}>{actionText}</button>
      );
    } else if (actionLink) {
      return (
        <NavLink to={actionLink} className="btn btn-info text-white">{actionText}</NavLink>
      );
    }
  };

  useEffect(() => {
    if (!permission) {
      setTextPermission(listUrl)
    }
  }, [permission])

  const isAllowed = () => {
    if (!textPermission) return false
    const hasPermission = userCan(textPermission, method)
    return hasPermission;
  };

  return (
    <div className='page-title shadow-sm p-2 rounded mb-3 row justify-content-between'>
      {
        listUrl && isAllowed() &&
        <div className={`col-6 col-md-1 col-lg-2 d-flex justify-content-start mb-2 mb-md-2`}>
          <div>
            <NavLink to={listUrl} className=' btn btn-outline-light shadow view-list'>
              <span className='d-flex align-items-center gap-1 text-dark'>List
                <Icon className='font-large' icon={`humbleicons:arrow-go-back`} />
              </span>
            </NavLink>
          </div>
        </div>
      }
      <div className={`col-12 ${listUrl && isAllowed() ? 'col-md-11 col-lg-10' : ''} d-flex justify-content-between align-items-center`}>
        <h3 className='heading'>{title}</h3>
        {isAllowed() && (
          <div className='ms-1 text-end text-nowrap'>
            <div>
              {renderAction()}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default PageHeader;
