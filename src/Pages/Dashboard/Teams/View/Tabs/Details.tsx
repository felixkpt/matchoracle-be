import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/Autos/SimpleTable'
import useListSources from '@/hooks/list-sources/useListSources'
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces'
import { subscribe, unsubscribe } from '@/utils/events'
import { useEffect } from 'react'
import { TeamInterface } from '@/interfaces/FootballInterface'
import AddSource from '@/components/AddSource'

type Props = {
    record: TeamInterface | undefined
    modelDetails: CollectionItemsInterface | undefined
}

const Details = ({ record, modelDetails }: Props) => {

    const { competitions: listSources } = useListSources()

    const addTeamSources = () => {
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
                    <SimpleTable exclude={['emblem']} modelDetails={modelDetails} record={record} listSources={listSources} />
                    <button type="button" className="btn btn-primary d-none" id="addTeamSourcesButton" data-bs-toggle="modal" data-bs-target="#addTeamSources"></button>
                </>
            }
            {
                record
                &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addTeamSourcesButton" data-bs-toggle="modal" data-bs-target="#addTeamSources"></button>
                    <GeneralModal title={`Add source for ${record.name || '#'}`} actionUrl={`dashboard/teams/view/${record.id}/add-sources`} size={'modal-lg'} id={`addTeamSources`}>
                        <AddSource record={record} />
                    </GeneralModal>

                </>
            }

        </div>
    )
}

export default Details