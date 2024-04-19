import { CompetitionTabInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionSubHeader from "../Inlcudes/CompetitionSubHeader"
import AutoTable from "@/components/AutoTable"
import { useEffect, useState } from "react"
import { appendFromToDates } from "@/utils/helpers"
import { oddsColumns } from '@/utils/constants';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const Odds: React.FC<Props> = ({ record, seasons, selectedSeason }) => {

    const competition = record
    const [useDate, setUseDates] = useState(false);
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>([undefined, undefined]);

    const [baseUri, setBaseUri] = useState('')

    useEffect(() => {

        if (competition) {
            let uri = `admin/competitions/view/${competition.id}/odds?type=all`
            if (useDate) {
                uri = uri + `${appendFromToDates(useDate, fromToDates)}`
            } else {
                uri = uri + `&season_id=${selectedSeason ? selectedSeason?.id : ''}`
            }
            setBaseUri(uri)
        }
    }, [competition, fromToDates])

    return (
        <div>
            {
                competition &&
                <div>
                    <div className='shadow-sm'>
                        <CompetitionSubHeader record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />
                    </div>
                    {baseUri &&
                        <AutoTable key={baseUri} columns={oddsColumns} baseUri={baseUri} search={true} tableId={'competitionMatchesTable'} customModalId="teamModal" />
                    }
                </div>
            }
        </div>
    )
}

export default Odds