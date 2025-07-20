import GeneralModal from '@/components/Modals/GeneralModal'
import SimpleTable from '@/components/Autos/SimpleTable'
import { CountryInterface } from '@/interfaces/FootballInterface'

type Props = {
    country: CountryInterface | undefined
}

const Details = ({ country }: Props) => {

    const recordLocal = country

    return (
        <div>
            {
                country
                &&
                <>
                    <SimpleTable htmls={['action']} record={country} />
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                </>
            }
            {
                recordLocal
                &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                    <GeneralModal title={`Add source for ${recordLocal.name || '#'}`} actionUrl={`dashboard/competitions/view/${recordLocal.id}/add-sources`} size={'modal-lg'} id={`addSource`}>
                        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Officiis, nulla.
                    </GeneralModal>

                </>
            }

        </div>
    )
}

export default Details