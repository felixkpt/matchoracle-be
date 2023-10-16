import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/SimpleTable'
import useListSources from '@/hooks/apis/useListSources'
import { CountryInterface } from '@/interfaces/CompetitionInterface'
import { useState } from 'react'

type Props = {
    country: CountryInterface
}

const Details = ({ country }: Props) => {

    const recordLocal = country?.data

    const { competitions: list_sources } = useListSources()
    const [key, setKey] = useState(0)

    return (
        <div>
            {
                country
                &&
                <>
                    <SimpleTable htmls={['action']} record={country} isNative={true} list_sources={list_sources} />
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                </>
            }
            {
                recordLocal
                &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                    <GeneralModal title={`Add source for ${recordLocal.name || '#'}`} actionUrl={`admin/competitions/view/${recordLocal.id}/add-sources`} size={'modal-lg'} id={`addSource`} setKey={setKey}>
                        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Officiis, nulla.
                    </GeneralModal>

                </>
            }

        </div>
    )
}

export default Details