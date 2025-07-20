import { useState } from 'react'
import AutoPageHeader from './AutoPageHeader'
import AutoTable from './AutoTable'
import AutoModal from './AutoModal'
import { ActionsType, CollectionItemsInterface, ColumnInterface, ListSourceInterface, ModalSizeType } from '@/interfaces/UncategorizedInterfaces'

type Props = {
    pluralName: string
    singularName: string
    uri: string
    columns: ColumnInterface[]
    actions?: ActionsType
    componentId: string
    search?: boolean
    listSources?: { [key: string]: () => Promise<ListSourceInterface[]> }
    modalSize?: ModalSizeType
}

const AutoPage = ({ pluralName, singularName, uri, columns, actions, componentId, search = true, listSources, modalSize }: Props) => {

    const [modelDetails, setModelDetails] = useState<Omit<CollectionItemsInterface, 'data'>>()

    return (
        <div>
            <div>
                <div>
                    <AutoPageHeader pluralName={pluralName} singularName={singularName} componentId={componentId} />
                    <AutoTable
                        baseUri={uri}
                        columns={columns}
                        actions={actions}
                        getModelDetails={setModelDetails}
                        search={search}
                        tableId={`${componentId}Table`}
                        listSources={listSources}
                        modalSize={modalSize}
                    />
                </div>
                {
                    modelDetails && <><AutoModal id={`${componentId}Modal`} modelDetails={modelDetails} actionUrl={uri} listSources={listSources} modalSize={modalSize} /></>
                }
            </div>
        </div>
    )
}

export default AutoPage