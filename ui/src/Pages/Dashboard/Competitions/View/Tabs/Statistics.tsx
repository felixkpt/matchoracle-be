import { CompetitionTabInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import GeneralModal from "@/components/Modals/GeneralModal"
import PredictionsStats from "../Inlcudes/PredictionsStats"
import ResultsStats from "../Inlcudes/ResultsStats"
import Str from "@/utils/Str"

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const Statistics: React.FC<Props> = ({ record, selectedSeason }) => {

    const competition = record

    if (!competition) return null

    return (
        <div>
            {
                competition
                &&
                <div className="mt-3">
                    <div key={selectedSeason?.id} className="row gap-4 gap-md-0">
                        <div className="col-md-6">
                            <ResultsStats competition={competition} selectedSeason={selectedSeason} />
                        </div>
                        <div className="col-md-6">
                            <PredictionsStats competition={competition} selectedSeason={selectedSeason} />
                        </div>
                    </div>

                    <GeneralModal title={`Predictions stats form`} actionUrl={`dashboard/competitions/view/${competition.id}/do-statistics`} size={'modal-lg'} id={`doStats`} >
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

export default Statistics