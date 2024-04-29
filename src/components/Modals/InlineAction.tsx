import { publish, subscribe, unsubscribe } from '@/utils/events';
import React, { ReactNode, useEffect } from 'react'

interface InlinActionProps {
    title?: string;
    children: ReactNode;
    actionUrl: string;
    id?: string
    setKey?: React.Dispatch<React.SetStateAction<number>>; // Use React.Dispatch type for setKey
}

const InlineAction: React.FC<InlinActionProps> = ({ title, children, actionUrl, id, setKey }) => {

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

    return <form encType="" id={id || 'inlineForm'} method="post" action-url={actionUrl} onSubmit={(e: any) => publish('ajaxPost', e)} >{children}</form>

}

export default InlineAction