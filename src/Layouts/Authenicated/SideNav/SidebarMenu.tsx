import React from 'react';

function handleToggle(key: string) {
  const target = document.getElementById(key);
  target?.classList.toggle('d-none');
}

const SidebarMenu = ({ menuItems }) => {
  const renderMenuItems = (items, indent) => {
    return items.map((item) => {
      if (item.hidden) return null;

      indent +=1

      const hasChildren = item.children && item.children.length > 0;
      const hasRoutes = item.routes && item.routes.length > 0;

      return (
        <React.Fragment key={item.folder}>
          <li className="nav-item">
            <a className="nav-link" href="#">
              {item.title}
            </a>
            {hasChildren && (
              <ul className={`list-unstyled nested-routes main ms-${indent}`}>
                

                <div className='folder-section toggler-section mb-2 px-1 border bg-white d-flex align-items-center justify-content-between rounded gap-2'>
          <div className='col-8 d-flex'>
            <label className='toggler text-base flex-grow-1 border flex-grow-1 py-2 ps-1 pe-0 rounded' onClick={() => handleToggle()}>tilt</label>
          </div>
          <div className='bg-light col-4 d-flex justify-content-between rounded'>
            <label>Icon
              <input style={{ width: '100px' }} type="text" id={`-folder-icon`} className='folder-icon ms-1 border border-secondary rounded' />
            </label>
            <label className='form-check-label d-flex align-items-center cursor-pointer'>
              <input
                type='checkbox'
                id={`${currentId}-folder-hidden`}
                className='form-check-input me-2 folder-hidden'
              />
              Hidden
            </label>
          </div>
        </div>

                {renderMenuItems(item.children, indent)}
              </ul>
            )}
            {hasRoutes && (
              <ul className="nav">
                {item.routes.map((route) => (
                  <li key={route.route} className="nav-item">
                    <a className="nav-link" href="#">
                      {route.title}
                    </a>
                  </li>
                ))}
              </ul>
            )}
          </li>
        </React.Fragment>
      );
    });
  };

  return (
    <ul className='list-unstyled nested-routes main'>
      {renderMenuItems(menuItems, 0)}
    </ul>
  );
};

export default SidebarMenu;
