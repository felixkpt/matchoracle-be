import AjaxPost from '@/components/AjaxPost';
import ToastNotification from '@/components/Modals/ToastNotification';
import PrepareEditModal from '@/components/PrepareEditModal';
import PrepareStatusUpdateModal from '@/components/PrepareStatusUpdateModal';
import PrepareViewModal from '@/components/PrepareViewModal';
import App from '@/utils/App';

export default function Footer() {

    return (
        <>
            <footer className="py-4 bg-body-secondary mt-auto">
                <div className="container-fluid px-4">
                    <div className="d-flex align-items-center justify-content-between small">
                        <div className="text-muted">Copyright &copy; {App.name()} 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
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
