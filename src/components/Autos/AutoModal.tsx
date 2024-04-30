import { useEffect, useRef, useState } from 'react';
import { publish } from '@/utils/events';
import { DataInterface, ListSourceInterface, ModalSizeType } from '@/interfaces/UncategorizedInterfaces';
import AutoModalBody from './AutoModalBody';
import Str from '../../utils/Str';
interface ModalProps {
    modelDetails?: any;
    record?: DataInterface | null
    modelName?: string;
    fillable?: { [key: string]: { input: string; type: string } };
    actionUrl?: string;
    id?: string
    setKey?: React.Dispatch<React.SetStateAction<number>>; // Use React.Dispatch type for setKey
    modalSize?: ModalSizeType
    listSources?: { [key: string]: () => Promise<ListSourceInterface[]> };
    listSelects?: any
}

const AutoModal: React.FC<ModalProps> = ({ modelDetails, record, actionUrl, modalSize, id, setKey, listSources, listSelects }) => {

    const [localKey, setLocalKey] = useState(0);
    const [modelName, setModelName] = useState([]);

    const [computedSize, setComputedSize] = useState<string>('')
    const modalId = id || 'AutoModal'

    let formId = 'AutoModalForm'
    if (modelDetails.tableId) {
        formId = Str.before(modelDetails.tableId, 'Table') + 'Form'
    } else if (modalId) {
        formId = Str.before(modalId, 'Modal') + 'Form'
    }

    useEffect(() => {
        if (Object.keys(modelDetails).length > 0) {
            setModelName(modelDetails?.model_name || null);
        }
    }, [modelDetails]);

    return (

        <div key={localKey} className={`modal fade automodal`} id={modalId} data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden='true'>
            <form method='post' id={formId} data-action={actionUrl} onSubmit={(e) => publish('autoPost', e)} className="flex justify-center">
                <div className={`modal-dialog ${computedSize}`}>
                    <div className="modal-content">
                        {modelDetails ?
                            <div>
                                <div className="modal-header">
                                    <h5 className="modal-title" id="staticBackdropLabel">{`${record && record.id ? 'Edit' : 'Create'} ${modelName}`}</h5>
                                    <button type="button" className="btn-close" onClick={() => setLocalKey(localKey + 1)} data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <AutoModalBody modelDetails={modelDetails} record={record} modalSize={modalSize} id={id} setKey={setKey} listSources={listSources} listSelects={listSelects} setComputedSize={setComputedSize} />
                            </div>
                            : 'Model data incomplete'
                        }
                    </div>
                </div>
            </form>
        </div>
    );
};

export default AutoModal;
