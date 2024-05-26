import { useAuth } from "@/contexts/AuthContext";
import useAxios from "@/hooks/useAxios";
import { useEffect } from "react";
import { NavLink } from "react-router-dom";
import { Icon } from "@iconify/react/dist/iconify.js";
import { toggleSidebar } from "../../Default/SideNav/Index";
import { config } from "@/utils/helpers";

interface Props {
    guestMode?: boolean
}
const NavBar = ({ guestMode }: Props) => {
    const { user } = useAuth();
    const { post } = useAxios();

    // logout user
    const handleLogout = async (e: any) => {
        e.preventDefault();

        post('/auth/logout').then((res) => {
            if (res) {
                localStorage.removeItem(`${config.storageName}.user`);
                setTimeout(() => {
                    window.location.href = '/';
                }, 6000);
            }
        })
    };

    useEffect(() => {
        const sidebarToggle = document.body.querySelector('#sidebarToggle');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        return () => {
            if (sidebarToggle) {
                sidebarToggle.removeEventListener('click', toggleSidebar);
            }
        }
    }, []);

    return (
        <nav className="sb-topnav navbar navbar-expand navbar-dark sb-navbar-dark shadow">
            <div className="navbar-brand ps-3 d-flex align-items-center justify-content-md-between">
                <span className="order-2 order-md-1">
                    <NavLink to="/" className='navbar-brand ps-3'>{config.name}</NavLink>
                </span>
                {
                    !guestMode &&
                    <button className="btn btn-link btn-sm me-4 me-lg-0 order-1 order-md-2" id="sidebarToggle"><Icon icon={`fa6-solid:bars`} /></button>
                }
            </div>
            {
                !guestMode &&
                <>
                    <form className="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                        <div className="input-group">
                            <input className="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                            <button className="btn bg-secondary text-white" id="btnNavbarSearch" type="button"><Icon icon="prime:bookmark"></Icon></button>
                        </div>
                    </form>
                    <ul className="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                        <li className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><Icon icon={`uiw:user`} /></a>
                            <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                {
                                    user ?
                                        <>
                                            <li><span className="dropdown-item disabled">{user?.name || 'Guest'}</span></li>
                                            <li><NavLink className="dropdown-item" to="/user/account">Profile</NavLink></li>
                                            <li><NavLink className="dropdown-item" to="/user/account?tab=login-logs">Login Logs</NavLink></li>
                                            <li><hr className="dropdown-divider" /></li>
                                            <li><a className="dropdown-item" href="#!" onClick={handleLogout}>Logout</a></li>
                                        </>
                                        :
                                        <>
                                            <li><NavLink className="dropdown-item" to="/login">Login</NavLink></li>
                                            <li><NavLink className="dropdown-item" to="/register">Register</NavLink></li>
                                        </>
                                }
                            </ul>
                        </li>
                    </ul>
                </>
            }
        </nav>
    );
};
export default NavBar