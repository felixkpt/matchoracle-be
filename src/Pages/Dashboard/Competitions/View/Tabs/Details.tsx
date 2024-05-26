import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/Autos/SimpleTable'
import useListSources from '@/hooks/list-sources/useListSources'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces'
import { subscribe, unsubscribe } from '@/utils/events'
import { useEffect } from 'react'
import { CompetitionInterface } from '@/interfaces/FootballInterface'
import AddSource from '@/components/AddSource'
import NoContentMessage from '@/components/NoContentMessage'

type Props = {
    record: CompetitionInterface | undefined
    modelDetails: CollectionItemsInterface | undefined
}

const Details = ({ record, modelDetails }: Props) => {

    const { competitions: listSources } = useListSources()

    const addSources = (e: Event) => {
    }

    useEffect(() => {

        subscribe('prepareModalAction', addSources)
        return () => unsubscribe('prepareModalAction', addSources)
    }, [record])

    useEffect(() => {
        const sel = document.querySelector('.dropdown-item.autotable-modal-add-sources')
        if (sel)
            sel.addEventListener('click', () => {
                document.getElementById('addSourcesButton')?.click()
            })

    }, [record])

    return (
        <div className='card mt-3'>
            <div className="card-body">
                {
                    record ?
                        <>
                            <SimpleTable exclude={['emblem']} modelDetails={modelDetails} record={record} listSources={listSources} />
                            <button type="button" className="btn btn-primary d-none" id="addSourcesButton" data-bs-toggle="modal" data-bs-target="#addSources"></button>
                            <button type="button" className="btn btn-primary d-none" id="addSourcesButton" data-bs-toggle="modal" data-bs-target="#addSources"></button>
                            <GeneralModal title={`Add source for ${record.name || '#'}`} actionUrl={`dashboard/competitions/view/${record.id}/add-sources`} size={'modal-lg'} id={`addSources`}>
                                <AddSource record={record} />
                            </GeneralModal>
                        </>
                        :
                        <NoContentMessage />
                }
            </div>
        </div>
    )
}

export default Details