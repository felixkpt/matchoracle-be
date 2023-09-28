import { publish, subscribe, unsubscribe } from '@/utils/events';
import { emitAjaxPost } from '@/utils/helpers'
import React, { ReactNode, useEffect, useState } from 'react'

interface GeneralModalProps {
    title?: string;
    children: ReactNode;
    actionUrl: string;
    id?: string
    setKey?: React.Dispatch<React.SetStateAction<number>>; // Use React.Dispatch type for setKey
    size?: 'modal-sm' | 'modal-lg' | 'modal-xl'
}

const GeneralModal: React.FC<GeneralModalProps> = ({ title, children, actionUrl, size, id, setKey }) => {

    const [computedSize, setComputedSize] = useState<string>('')

    useEffect(() => {

        if (size)
            setComputedSize(size)
        else if (length < 8)
            setComputedSize('modal-sm')
        else if (length < 16)
            setComputedSize('modal-lg')
        else if (length > 16)
            setComputedSize('modal-xl')
    }, [])


    useEffect(() => {

        subscribe('ajaxPostDone', handleAjaxPostDone);

        return () => {
            unsubscribe('ajaxPostDone', handleAjaxPostDone);
        };

    }, [])

    const handleAjaxPostDone = (resp: any) => {
        if (resp.detail) {
            const detail = resp.detail;
            if (detail.elementId === id && detail.results && setKey) {
                setTimeout(() => {
                    setKey((curr) => curr + 1);
                }, 300);
            }
        }
    };
    
    return (
        <div className={`modal fade`} id={`${id || 'GeneralModal'}`} data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden={`true`}>
            <div className={`modal-dialog ${computedSize}`}>
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title title" id="update_password_label">{title || 'New Password'}</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div className="modal-body">
                        <div className="section">
                            <form encType="" method="post" action-url={actionUrl} onSubmit={(e: any) => publish('ajaxPost', e)} >
                                {children}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default GeneralModal