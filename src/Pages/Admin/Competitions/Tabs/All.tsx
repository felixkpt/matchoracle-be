import AutoTable from '@/components/AutoTable'

type Props = {
    columns: []
    setModelDetails: React.Dispatch<React.SetStateAction<any>>
}

const All = ({ columns, setModelDetails }: Props) => {

    return (
        <div>
            <AutoTable columns={columns} baseUri={`admin/competitions?is_odds_enabled=0`} search={true} getModelDetails={setModelDetails} tableId={'allCompetitionsTable'} customModalId="competitionModal" />
        </div>
    )
}

export default All