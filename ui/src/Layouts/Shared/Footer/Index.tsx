import AutoPost from '@/components/Autos/AutoPost';
import ToastNotification from '@/components/Modals/ToastNotification';
import PrepareEditModal from '@/components/PrepareEditModal';
import PrepareStatusUpdateModal from '@/components/PrepareStatusUpdateModal';
import PrepareViewModal from '@/components/PrepareViewModal';
import { config } from '../../../utils/helpers';
import { NavLink } from 'react-router-dom';

export default function Footer() {

    return (
        <>
            <footer className="py-4 mx-2 footer-section mt-auto footer rounded">
                <div className="container-fluid px-4">
                    <div className="row align-items-center justify-content-between">
                        <div className="col-md-6">
                            <div className="cursor-default d-flex align-items-center justify-content-center justify-content-md-start gap-1">
                                <span>Copyright &copy; <NavLink to='/' className='text-white'>{config.name}</NavLink></span><span>{config.version}</span><span>{config.release}</span>
                            </div>
                        </div>
                        <div className="col-md-6">
                            <div className='d-flex gap-2 justify-content-center justify-content-md-end'>
                                <NavLink to="/privacy-policy" className='text-white'>Privacy Policy</NavLink>
                                <span>&middot;</span>
                                <NavLink to="/terms-and-conditions" className='text-white'>Terms &amp; Conditions</NavLink>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <div>
                <ToastNotification />
                <PrepareViewModal />
                <PrepareEditModal />
                <PrepareStatusUpdateModal />
                <AutoPost />
            </div>
        </>
    );
}
