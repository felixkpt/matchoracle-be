import CompetitionsList from '@/Pages/Dashboard/Teams/Includes/CompetitionsList'
import Loader from '@/components/Loader'
import useAxios from '@/hooks/useAxios'
import { CompetitionInterface, CountryInterface } from '@/interfaces/FootballInterface'
import { AxiosResponse } from 'axios'
import { useEffect, useState } from 'react'

type Props = {
    country: CountryInterface | undefined
}

const Competitions = ({ country }: Props) => {

    const { get } = useAxios()
    const [competitions, setCompetitions] = useState<CompetitionInterface[]>()

    useEffect(() => {

        if (country)
            get(`/dashboard/competitions/country/${country.id}`).then((res: AxiosResponse) => {
                if (res) {
                    setCompetitions(res.data)
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