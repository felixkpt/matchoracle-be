import AutoTable from '@/components/Autos/AutoTable'
import { ActionsType, ColumnInterface } from '@/interfaces/UncategorizedInterfaces'

type Props = {
    columns: ColumnInterface[]
    actions?: ActionsType
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const All = ({ columns, actions, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} actions={actions} baseUri={`dashboard/competitions?is_odds_enabled=0`} search={true} getModelDetails={setModelDetails} tableId={'allCompetitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default All