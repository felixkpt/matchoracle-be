import CompetitionsList from '@/Pages/Admin/Teams/Includes/CompetitionsList'
import { CompetitionInterface, CountryInterface } from '@/interfaces/CompetitionInterface'
import React from 'react'

type Props = {
    country: CountryInterface
    competitions: CompetitionInterface[]
}

const Competitions = ({ country, competitions }: Props) => {
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