import { useAuth } from "@/contexts/AuthContext";
import useAxios from "@/hooks/useAxios";
import React, { useEffect } from "react";
import { NavLink } from "react-router-dom";
import { Icon } from "@iconify/react/dist/iconify.js";
import { toggleSidebar } from "../SideNav/Index";
import App from "@/utils/App";

const NavBar = () => {
    const { user } = useAuth();
    const { post, loading } = useAxios();

    // logout user
    const handleLogout = async (e: any) => {
        e.preventDefault();
        try {
            await post('/logout');
            localStorage.removeItem('user');
            window.location.href = '/';
        } catch (error) {
            console.log(error);
        }
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
                    <NavLink to="/" className='navbar-brand ps-3'>{App.name()}</NavLink>
                </span>
                <button className="btn btn-link btn-sm me-4 me-lg-0 order-1 order-md-2" id="sidebarToggle"><Icon icon={`fa6-solid:bars`} /></button>
            </div>
            <form className="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div className="input-group">
                    <input className="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button className="btn btn-primary" id="btnNavbarSearch" type="button"><i className="fas fa-search"></i></button>
                </div>
            </form>
            <ul className="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li className="nav-item dropdown">
                    <a className="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><Icon icon={`uiw:user`} /></a>
                    <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><span className="dropdown-item disabled">{user?.name || 'Guest'}</span></li>
                        <li><NavLink className="dropdown-item" to="/user/profile">Profile</NavLink></li>
                        <li><a className="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr className="dropdown-divider" /></li>
                        <li><a className="dropdown-item" href="#!" onClick={handleLogout}>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    );
};
export default NavBar