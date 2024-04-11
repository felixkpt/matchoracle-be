import AutoTable from '@/components/AutoTable'
import React from 'react'

type Props = {
    columns: []
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const OddsEnabled = ({ columns, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} baseUri={`admin/competitions?is_odds_enabled=1`} search={true} getModelDetails={setModelDetails} tableId={'OddsEnabledCompetitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default OddsEnabled