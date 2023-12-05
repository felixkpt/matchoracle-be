import { CompetitionTabInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AsyncSeasonsList from "../Inlcudes/AsyncSeasonsList"
import AutoTable from "@/components/AutoTable"
import Str from "@/utils/Str"
import { useState } from "react"

const Predictions: React.FC<CompetitionTabInterface> = ({ record, selectedSeason, setSelectedSeason, setKey }) => {

    const competition = record
    const [key, setLocalKey] = useState(0);
    const [startDate, setStartDate] = useState(null);
    const [useDate, setUseDate] = useState(false);

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
                    <CompetitionHeader title="Predictions" record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} startDate={startDate} setStartDate={setStartDate} setUseDate={setUseDate} setLocalKey={setLocalKey} />

                    <AutoTable key={key} columns={columns} baseUri={`admin/competitions/view/${competition.id}/predictions?season_id=${selectedSeason ? selectedSeason?.id : ''}&date=${useDate ? startDate : ''}&break_preds=1`} search={true} tableId={'matchesTable'} customModalId="teamModal" />

                    <GeneralModal title={`Predictions form`} actionUrl={`admin/competitions/view/${competition.id}/predict`} size={'modal-lg'} id={`doPredictions`} setKey={setKey}>
                        <div className="form-group mb-3">
                            <label htmlFor="season_id">Season</label>
                            <AsyncSeasonsList record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} useDate={useDate} />
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

export default Predictions