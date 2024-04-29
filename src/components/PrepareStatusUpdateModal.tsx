import { useEffect, useState } from 'react'
import { publish, subscribe, unsubscribe } from '@/utils/events'
import Select, { PropsValue } from 'react-select';
import Str from '@/utils/Str';

const PrepareStatusUpdateModal = () => {

    const [statuses, setStatuses] = useState([])
    const [record, setRecord] = useState<any>(false)
    const [selected, setSelected] = useState<PropsValue<object> | null>();

    const [actionUrl, setActionUrl] = useState<string>('')

    const prepareStatusUpdate = async (event: CustomEvent<{ [key: string]: any }>) => {

        const detail = event?.detail
        if (detail) {
            setStatuses(detail.modelDetails.statuses)
            setRecord(detail.record)
            setActionUrl(detail.action)
        }

        document.getElementById("showStatusUpdate")?.click()

    }

    useEffect(() => {

        if (record) {
            const status = statuses.find((status: any) => status.id === record.status_id)
            if (status) setSelected(status)
            else setSelected(null)
        }

    }, [record])

    useEffect(() => {

        // Add event listener for the custom ajaxPost event
        const prepareEventListener: EventListener = (event) => {

            const customEvent = event as CustomEvent<{ [key: string]: any }>;
            if (customEvent.detail) {
                prepareStatusUpdate(customEvent);
            }
        };

        subscribe('prepareStatusUpdate', prepareEventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('prepareStatusUpdate', prepareEventListener);
        };
    }, []);

    return (
        <div>
            <button id='showStatusUpdate' type="button" className="btn btn-info text-white d-none" data-bs-toggle="modal" data-bs-target="#StatusUpdate"></button>
            <div className={`modal fade`} id="StatusUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden={`true`}>

                <div className="modal-dialog modal-dialog-top animated zoomIn animated-3x   ">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title title" id="StatusUpdate_label">Update status</h5>
                            <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div className="modal-body">
                            <div className="section">
                                <form method="post" action-url={actionUrl} onSubmit={(e: any) => publish('ajaxPost', e)} >
                                    <input type="hidden" name="_method" value="patch" />
                                    <div className='form-group'>
                                        {
                                            (record && (selected || selected === null)) ?
                                                <Select
                                                    key={record.id}
                                                    className="form-control"
                                                    classNamePrefix="select"
                                                    value={selected}
                                                    isDisabled={false}
                                                    isLoading={false}
                                                    isClearable={false}
                                                    isSearchable={true}
                                                    name="status_id"
                                                    options={statuses}
                                                    onChange={(val) => setSelected(val)}
                                                    getOptionValue={(option: any) => `${option['id']}`}
                                                    getOptionLabel={(option: any) => Str.title(`${option['name']}`)}
                                                />
                                                : 'Loading record...'
                                        }
                                    </div>
                                    <div className="form-group mt-2 text-end">
                                        <button type="submit" className="btn btn-primary submit-btn ">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    )
}

export default PrepareStatusUpdateModal