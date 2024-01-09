import { NavLink } from "react-router-dom";
import { Icon } from '@iconify/react/dist/iconify.js';
import DashMiniCard from "../DashMiniCard";

const UsersMiniCardSection = ({ to, icon, title, total, active, inactive }) => {
  return (
    <div className="col-md-6 col-xl-4 mb-4">
      <NavLink to={to} className={'link-unstyled'}>
        <div className="card shadow">
          <div className="card-header bg-secondary text-white">
            <h5 className='d-flex align-items-center gap-1'>
              <Icon width={'2rem'} icon={icon} />
              <span>{title}</span>
            </h5>
          </div>
          <div className="card-body text-center">
            <DashMiniCard total={total} active={active} inactive={inactive} />
          </div>
        </div>
      </NavLink>
    </div>
  );
};

export default UsersMiniCardSection;
