import { useEffect, useRef } from 'react';
import Logo from '@/images/logomin.png';
import { NavLink } from 'react-router-dom';

interface HeaderProps {
    sidebar: any,
    setSidebarOpen: any,
    sidebarOpen: any
}

const Header = ({ sidebar, setSidebarOpen, sidebarOpen }: HeaderProps) => {

    const trigger = useRef<any>(null);

    // close on click outside
    useEffect(() => {
        const clickHandler = ({ target }: MouseEvent) => {
            if (!sidebar.current || !trigger.current) return

            if (
                !sidebarOpen ||
                sidebar.current.contains(target) ||
                trigger.current.contains(target)
            ) return
            else
                setSidebarOpen(false);
        };
        document.addEventListener('click', clickHandler);
        return () => document.removeEventListener('click', clickHandler);
    });

    // close if the esc key is pressed
    useEffect(() => {
        const keyHandler = ({ keyCode }: KeyboardEvent) => {
            if (!sidebarOpen || keyCode !== 27) return;
            setSidebarOpen(false);
        };
        document.addEventListener('keydown', keyHandler);
        return () => document.removeEventListener('keydown', keyHandler);
    });

    return (
        <div className="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5 mb-4">
            <NavLink to="/" className='flex w-full justify-center'>
                <img className='logo w-full' src={Logo} alt="Logo" />
            </NavLink>
            <hr />
        </div>
    )
}

export default Header