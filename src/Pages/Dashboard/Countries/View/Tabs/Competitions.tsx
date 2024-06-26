import CompetitionsList from '@/Pages/Dashboard/Teams/Includes/CompetitionsList'
import Loader from '@/components/Loader'
import useAxios from '@/hooks/useAxios'
import { CompetitionInterface, CountryInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    country: CountryInterface | undefined
}

const Competitions = ({ country }: Props) => {

    const { get } = useAxios()
    const [competitions, setCompetitions] = useState<CompetitionInterface[]>()

    useEffect(() => {

        if (country)
            get(`/dashboard/competitions/country/${country.id}`).then((response) => {
                if (response.results) {
                    setCompetitions(response.results.data)
                }
            })

    }, [country])

    return (
        <div>
            {
                country && competitions ?
                    <CompetitionsList country={country} competitions={competitions} />
                    :
                    <Loader />
            }
        </div>
    )
}

export default Competitions