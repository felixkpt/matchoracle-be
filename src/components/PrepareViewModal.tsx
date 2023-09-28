import { useEffect, useState } from 'react'
import { subscribe, unsubscribe } from '@/utils/events'
import ViewModal from './Modals/ViewModal'

const PrepareViewModal = () => {

    const [modelDetails, setModelDetails] = useState({})
    const [record, setRecord] = useState<any>(false)

    const prepareView = async (event: CustomEvent<{ [key: string]: any }>) => {

        const detail = event?.detail
        if (detail) {
            setModelDetails({ ...detail.modelDetails, exclude: ['id', 'action'] })
            setRecord(detail.record)
        }

        document.getElementById("showViewModal")?.click()

    }

    useEffect(() => {

        // Add event listener for the custom ajaxPost event
        const prepareEventListener: EventListener = (event) => {

            const customEvent = event as CustomEvent<{ [key: string]: any }>;
            if (customEvent.detail) {
                prepareView(customEvent);
            }
        };

        subscribe('prepareView', prepareEventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('prepareView', prepareEventListener);
        };
    }, []);

    return (
        <div>
            <button id='showViewModal' type="button" className="btn btn-info text-white d-none" data-bs-toggle="modal" data-bs-target="#ViewModal"></button>
            <ViewModal record={record} modelDetails={modelDetails} />
        </div>
    )
}

export default PrepareViewModal