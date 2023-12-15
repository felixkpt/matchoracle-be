import { CompetitionTabInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AsyncSeasonsList from "../Inlcudes/AsyncSeasonsList"
import AutoTable from "@/components/AutoTable"
import { useState } from "react"
import FormatDate from "@/utils/FormatDate"
import { appendFromToDates } from "@/utils/helpers"

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

const PastPredictions: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

    const competition = record
    const [key, setLocalKey] = useState(0);
    const [useDate, setUseDates] = useState(false);

    const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);

    const columns = [
        { key: 'Game' },
        { key: '1X2' },
        { key: 'Pick' },
        { key: 'BTS' },
        { key: 'Over25' },
        { key: 'CS' },
        { label: 'half_time', key: 'Halftime' },
        { label: 'full_time', key: 'Fulltime' },
        { label: 'Status', key: 'Status' },
        { key: 'utc_date' },
        { label: 'Created At', key: 'Created_at' },
        { label: 'Action', key: 'action' },
    ]

    return (
        <div>
            {
                competition
                &&
                <div>
                    <CompetitionHeader title="Predictions" record={competition} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} setLocalKey={setLocalKey} />

                    <AutoTable key={key} columns={columns} baseUri={`admin/competitions/view/${competition.id}/predictions?show_predictions=1&break_preds=1&season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}&type=past`} search={true} tableId={'matchesTable'} customModalId="teamModal" />

                    <GeneralModal title={`Predictions form`} actionUrl={`admin/competitions/view/${competition.id}/predict`} size={'modal-lg'} id={`doPredictions`} setKey={setKey}>
                        <div className="form-group mb-3">
                            <label htmlFor="season_id">Season</label>
                            <AsyncSeasonsList seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />
                        </div>
                        <div className="form-group mb-3">
                            <label htmlFor="matchday">Match day</label>
                            <input type="number" min={0} max={200} name='matchday' id='matchday' className='form-control' />
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

export default PastPredictions