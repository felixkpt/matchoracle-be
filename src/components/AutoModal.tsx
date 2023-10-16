import { useEffect, useRef, useState } from 'react';
import { publish, subscribe, unsubscribe } from '@/utils/events';
import RenderAsyncSelect from './RenderAsyncSelect';
import { DataInterface, ListSourceInterface, ModalSizeType } from '@/interfaces/UncategorizedInterfaces';
import Str from '@/utils/Str';
import AutoModalBody from './AutoModalBody';
interface ModalProps {
    modelDetails?: any;
    record?: DataInterface | null
    modelName?: string;
    fillable?: { [key: string]: { input: string; type: string } };
    actionUrl?: string;
    id?: string
    setKey?: React.Dispatch<React.SetStateAction<number>>; // Use React.Dispatch type for setKey
    modalSize?: ModalSizeType
    list_sources?: { [key: string]: () => Promise<ListSourceInterface[]> };
    list_selects?: any
}

const AutoModal: React.FC<ModalProps> = ({ modelDetails, record, actionUrl, modalSize, id, setKey, list_sources, list_selects }) => {

    const [localKey, setLocalKey] = useState(0);
    const [modelName, setModelName] = useState([]);

    const [computedSize, setComputedSize] = useState<string>('')
    const modalId = id || 'AutoModal'

    useEffect(() => {
        if (Object.keys(modelDetails).length > 0) {
            setModelName(modelDetails?.model_name || null);
        }
    }, [modelDetails]);

    const formRef = useRef<HTMLFormElement>(null);

    return (
        <form ref={formRef} method='post' action-url={actionUrl} onSubmit={(e: any) => publish('ajaxPost', e)} className="flex justify-center">

            <div key={localKey} className={`modal fade`} id={modalId} data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden='true'>
                <div className={`modal-dialog ${computedSize}`}>
                    <div className="modal-content">
                        {modelDetails ?
                            <div>
                                <div className="modal-header">
                                    <h5 className="modal-title" id="staticBackdropLabel">{`${record && record.id ? 'Edit' : 'Create'} ${modelName}`}</h5>
                                    <button type="button" className="btn-close" onClick={() => setLocalKey(localKey + 1)} data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <AutoModalBody modelDetails={modelDetails} record={record} modalSize={modalSize} id={id} setKey={setKey} list_sources={list_sources} list_selects={list_selects} setComputedSize={setComputedSize} />
                            </div>
                            : 'Model data incomplete'
                        }
                    </div>
                </div>
            </div>
        </form>
    );
};

export default AutoModal;
