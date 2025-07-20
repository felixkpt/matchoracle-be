import { NavLink } from "react-router-dom";
import { Icon } from '@iconify/react/dist/iconify.js';
import DashMiniCard from "../DashMiniCard";
import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";

interface Props {
  loading: boolean
  errors: any
  to: any
  icon: any
  title: any
  total: any
  active: any
  inactive: any
}

const UsersMiniCardSection = ({ loading, errors, to, icon, title, total, active, inactive }: Props) => {
  
  return (
    <div className="col-md-6 col-xl-4 mb-4">
      <NavLink to={to} className={'link-unstyled'}>
        <div className="card card-primary">
          <div className="card-header">
            <h5 className='d-flex align-items-center gap-1'>
              <Icon width={'2rem'} icon={icon} />
              <span>{title}</span>
            </h5>
          </div>
          <div className="card-body text-center">
            {
              loading ?
                <Loader />
                :
                <>
                  {
                    errors ?
                      <>
                      <NoContentMessage message="Data error" />
                      </>
                      :
                      <DashMiniCard total={total} active={active} inactive={inactive} />
                  }</>
            }
          </div>
        </div>
      </NavLink>
    </div>
  );
};

export default UsersMiniCardSection;
