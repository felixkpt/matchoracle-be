import CompetitionsList from '@/Pages/Admin/Teams/Includes/CompetitionsList'
import useAxios from '@/hooks/useAxios'
import { CompetitionInterface, CountryInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    country: CountryInterface
}

const Competitions = ({ country }: Props) => {

    const { get, loading } = useAxios()
    const [competitions, setCompetitions] = useState<CompetitionInterface>()

    useEffect(() => {

        get(`admin/competitions/country/${country.id}`).then((res: any) => {
            if (res) {
                setCompetitions(res.data)
            }
        })

    }, [country])

    return (
        <div>
            {
                country && competitions &&
                <CompetitionsList country={country} competitions={competitions} />
            }
        </div>
    )
}

export default Competitions