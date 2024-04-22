import AjaxPost from '@/components/AjaxPost';
import ToastNotification from '@/components/Modals/ToastNotification';
import PrepareEditModal from '@/components/PrepareEditModal';
import PrepareStatusUpdateModal from '@/components/PrepareStatusUpdateModal';
import PrepareViewModal from '@/components/PrepareViewModal';
import { config } from '../../../utils/helpers';
import { NavLink } from 'react-router-dom';

export default function Footer() {

    return (
        <>
            <footer className="py-4 bg-body-secondary mt-auto footer">
                <div className="container-fluid px-4">
                    <div className="row align-items-center justify-content-between small">
                        <div className="col-md-6">
                            <div className="text-muted d-flex align-items-center justify-content-center justify-content-md-start gap-1">
                                <span>Copyright &copy; {config.name}</span><span>{config.version}</span><span>{config.release}</span>
                            </div>
                        </div>
                        <div className="col-md-6">
                            <div className='d-flex gap-2 justify-content-center justify-content-md-end'>
                                <NavLink to="/privacy-policy">Privacy Policy</NavLink>
                                <span>&middot;</span>
                                <NavLink to="/terms-and-conditions">Terms &amp; Conditions</NavLink>
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
                <AjaxPost />
            </div>
        </>
    );
}
