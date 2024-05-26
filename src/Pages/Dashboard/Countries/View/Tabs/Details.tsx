import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/Autos/SimpleTable'
import useListSources from '@/hooks/list-sources/useListSources'
import { CountryInterface } from '@/interfaces/FootballInterface'
import { useState } from 'react'

type Props = {
    country: CountryInterface
}

const Details = ({ country }: Props) => {

    const recordLocal = country?.data

    const { competitions: listSources } = useListSources()
    const [key, setKey] = useState(0)

    return (
        <div>
            {
                country
                &&
                <>
                    <SimpleTable htmls={['action']} record={country} isNative={true} listSources={listSources} />
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                </>
            }
            {
                recordLocal
                &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                    <GeneralModal title={`Add source for ${recordLocal.name || '#'}`} actionUrl={`dashboard/competitions/view/${recordLocal.id}/add-sources`} size={'modal-lg'} id={`addSource`} setKey={setKey}>
                        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Officiis, nulla.
                    </GeneralModal>

                </>
            }

        </div>
    )
}

export default Details