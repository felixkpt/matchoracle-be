import AutoTable from '@/components/AutoTable'
import React from 'react'

type Props = {
    columns: []
    active_only: boolean
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const OddsEnabled = ({ columns, active_only, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} baseUri={`admin/competitions?is_odds_enabled=${1}&active_only=${active_only ? 1 : 0}`} search={true} getModelDetails={setModelDetails} tableId={'competitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default OddsEnabled