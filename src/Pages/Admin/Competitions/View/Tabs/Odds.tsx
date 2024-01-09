import { CompetitionTabInterface, SeasonInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionSubHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AutoTable from "@/components/AutoTable"
import { useEffect, useState } from "react"
import FormatDate from "@/utils/FormatDate"
import { appendFromToDates } from "@/utils/helpers"
import { oddsColumns, predictionsColumns } from '@/utils/constants';
import Str from "@/utils/Str"


interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const Odds: React.FC<Props> = ({ record, seasons, selectedSeason }) => {

    const competition = record
    const [useDate, setUseDates] = useState(false);

    const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);

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
                    <CompetitionHeader title="Odds" record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />

                    {baseUri &&
                        <AutoTable key={baseUri} columns={oddsColumns} baseUri={baseUri} search={true} tableId={'matchesTable'} customModalId="teamModal" />
                    }
                </div>
            }
        </div>
    )
}

export default Odds