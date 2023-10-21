import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/SimpleTable'
import useListSources from '@/hooks/apis/useListSources'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces'
import { subscribe, unsubscribe } from '@/utils/events'
import { useEffect } from 'react'
import { CompetitionInterface } from '@/interfaces/FootballInterface'
import AddSource from '@/components/AddSource'

type Props = {
    record: CompetitionInterface
    modelDetails: CollectionItemsInterface | undefined
}

const Details = ({ record, modelDetails }: Props) => {

    const { competitions: list_sources } = useListSources()

    const addTeamSources = (e: Event) => {
    }

    useEffect(() => {

        subscribe('prepareModalAction', addTeamSources)
        return () => unsubscribe('prepareModalAction', addTeamSources)
    }, [record])

    useEffect(() => {
        const sel = document.querySelector('.dropdown-item.autotable-modal-add-sources')
        if (sel)
            sel.addEventListener('click', () => {
                document.getElementById('addTeamSourcesButton')?.click()
            })

    }, [record])

    return (
        <div>
            {
                record
                &&
                <>
                    <SimpleTable exclude={['emblem']} modelDetails={modelDetails} record={record} list_sources={list_sources} />
                    <button type="button" className="btn btn-primary d-none" id="addTeamSourcesButton" data-bs-toggle="modal" data-bs-target="#addTeamSources"></button>
                </>
            }
            {
                record
                &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addTeamSourcesButton" data-bs-toggle="modal" data-bs-target="#addTeamSources"></button>
                    <GeneralModal title={`Add source for ${record.name || '#'}`} actionUrl={`admin/teams/view/${record.id}/add-sources`} size={'modal-lg'} id={`addTeamSources`}>
                        <AddSource record={record} />
                    </GeneralModal>

                </>
            }

        </div>
    )
}

export default Details