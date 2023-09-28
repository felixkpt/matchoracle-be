import React, { useEffect, useState } from 'react'
import { subscribe, unsubscribe } from '@/utils/events'
import AutoModal from './AutoModal'
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces'

const PrepareEditModal = () => {

    const [modelDetails, setModelDetails] = useState({})
    const [record, setRecord] = useState<any>(undefined)
    const [list_sources, setListSources] = useState<{ [key: string]: () => Promise<ListSourceInterface[]> }>()

    const [action, setActionUrl] = useState<string>('')
    const [modalSize, setModalSize] = useState(undefined)

    const prepareEdit = async (event: CustomEvent<{ [key: string]: any }>) => {

        const detail = event?.detail
        if (detail && detail.modelDetails) {
            setModelDetails(detail.modelDetails)
            setRecord(detail.record)
            setActionUrl(detail.action)
            setListSources(detail.list_sources)

            if (detail.modalSize) {
                setModalSize(detail.modalSize)
            }
        }

    }

    useEffect(() => {
        if (record) {
            document.getElementById("showAutoModal")?.click()
        }
    }, [record])

    useEffect(() => {

        // Add event listener for the custom ajaxPost event
        const prepareEventListener: EventListener = (event) => {

            const customEvent = event as CustomEvent<{ [key: string]: any }>;
            if (customEvent.detail) {
                prepareEdit(customEvent);
            }
        };

        subscribe('prepareEdit', prepareEventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('prepareEdit', prepareEventListener);
        };
    }, []);

    // // Wrap AutoModal with React.memo to optimize rendering
    const MemoizedAutoModal = React.memo(AutoModal,
        (prevProps, nextProps) => prevProps.modelDetails === nextProps.modelDetails
    );

    return (
        <div>
            {record &&
                <>
                    <button id='showAutoModal' type="button" className="btn btn-info text-white d-none" data-bs-toggle="modal" data-bs-target="#AutoModalEdit"></button>

                    <MemoizedAutoModal
                        key={record.id}
                        modelDetails={modelDetails}
                        record={record}
                        actionUrl={action}
                        list_sources={list_sources}
                        id='AutoModalEdit'
                        modalSize={modalSize}
                    />
                </>
            }
        </div>
    )
}

export default PrepareEditModal