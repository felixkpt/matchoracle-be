import { CompetitionTabInterface, SeasonInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionSubHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AsyncSeasonsList from "../Inlcudes/AsyncSeasonsList"
import AutoTable from "@/components/AutoTable"
import { useEffect, useState } from "react"
import FormatDate from "@/utils/FormatDate"
import { appendFromToDates } from "@/utils/helpers"
import { predictionsColumns } from '@/utils/constants';
import Str from "@/utils/Str"

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const UpcomingPredictions: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason }) => {

    const competition = record
    const [localSelectedSeason, setLocalSelectedSeason] = useState<SeasonInterface | null>(selectedSeason);
    const [useDate, setUseDates] = useState(false);

    const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);

    function handleSeasonChange(season: SeasonInterface) {
        setSelectedSeason(season)
        setLocalSelectedSeason(season)
    }

    const [baseUri, setBaseUri] = useState('')

    useEffect(() => {

        if (competition) {
            let uri = `admin/competitions/view/${competition.id}/predictions?show_predictions=1&break_preds=1&type=upcoming`
            if (useDate) {
                uri = uri + `${appendFromToDates(useDate, fromToDates)}`
            }
            setBaseUri(uri)
        }
    }, [competition, fromToDates])

    return (
        <div>
            {
                competition &&
                <div>
                    <CompetitionHeader title="Upcoming Predictions" actionTitle="Do Predictions" actionButton="doPredictions" record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />

                    {baseUri &&
                        <AutoTable key={baseUri} columns={predictionsColumns} baseUri={baseUri} search={true} tableId={'matchesTable'} customModalId="teamModal" />
                    }
                    <GeneralModal title={`Predictions form`} actionUrl={`admin/competitions/view/${competition.id}/predict`} size={'modal-lg'} id={`doPredictions`}>
                        <div className="form-group mb-3">
                            <label htmlFor="season_id">Selected season {
                                selectedSeason
                                &&
                                <span>
                                    {`${Str.before(selectedSeason.start_date, '-')} / ${Str.before(selectedSeason.end_date, '-')}`}
                                </span>
                            } </label>
                            <input type="hidden" name="season_id" key={selectedSeason?.id} value={selectedSeason?.id} />
                        </div>
                        <div className="modal-footer gap-1">
                            <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" className="btn btn-primary">Submit</button>
                        </div>
                    </GeneralModal>
                </div>
            }
        </div>
    )
}

export default UpcomingPredictions