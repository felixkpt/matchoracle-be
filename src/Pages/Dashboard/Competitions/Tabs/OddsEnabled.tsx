import AutoTable from '@/components/Autos/AutoTable'
import { ActionsType, ColumnInterface } from '@/interfaces/UncategorizedInterfaces'
import React from 'react'

type Props = {
    columns: ColumnInterface[]
    actions?: ActionsType
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const OddsEnabled = ({ columns, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} actions={actions} baseUri={`dashboard/competitions?is_odds_enabled=1`} search={true} getModelDetails={setModelDetails} tableId={'OddsEnabledCompetitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default OddsEnabled