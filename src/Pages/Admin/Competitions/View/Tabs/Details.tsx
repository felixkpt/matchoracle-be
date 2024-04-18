import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/SimpleTable'
import useListSources from '@/hooks/apis/useListSources'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces'
import { subscribe, unsubscribe } from '@/utils/events'
import { useEffect } from 'react'
import { CompetitionInterface } from '@/interfaces/FootballInterface'
import AddSource from '@/components/AddSource'
import NoContentMessage from '@/components/NoContentMessage'
import PageHeader from '@/components/PageHeader'

type Props = {
    record: CompetitionInterface | undefined
    modelDetails: CollectionItemsInterface | undefined
}

const Details = ({ record, modelDetails }: Props) => {

    const { competitions: list_sources } = useListSources()

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
        <div>
            <PageHeader title='Details' />
            <div className='card'>
                <div className="card-body">
                    {
                        record ?
                            <>
                                <SimpleTable exclude={['emblem']} modelDetails={modelDetails} record={record} list_sources={list_sources} />
                                <button type="button" className="btn btn-primary d-none" id="addSourcesButton" data-bs-toggle="modal" data-bs-target="#addSources"></button>
                                <button type="button" className="btn btn-primary d-none" id="addSourcesButton" data-bs-toggle="modal" data-bs-target="#addSources"></button>
                                <GeneralModal title={`Add source for ${record.name || '#'}`} actionUrl={`admin/competitions/view/${record.id}/add-sources`} size={'modal-lg'} id={`addSources`}>
                                    <AddSource record={record} />
                                </GeneralModal>
                            </>
                            :
                            <NoContentMessage />
                    }
                </div>
            </div>

        </div>
    )
}

export default Details