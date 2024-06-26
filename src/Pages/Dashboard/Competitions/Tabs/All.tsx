import AutoTable from '@/components/Autos/AutoTable'
import { ColumnInterface } from '@/interfaces/UncategorizedInterfaces'

type Props = {
    columns: ColumnInterface[]
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const All = ({ columns, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} baseUri={`dashboard/competitions?is_odds_enabled=0`} search={true} getModelDetails={setModelDetails} tableId={'allCompetitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default All