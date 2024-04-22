import { CompetitionTabInterface, PredictionModeInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionSubHeader from "../Inlcudes/CompetitionSubHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AutoTable from "@/components/AutoTable"
import { useEffect, useState } from "react"
import { appendFromToDates } from "@/utils/helpers"
import { predictionModes, predictionsColumns } from '@/utils/constants';
import Str from "@/utils/Str"
import PredictionStatsTable from "@/components/Predictions/PredictionStatsTable"
import { CollectionItemsInterface } from "@/interfaces/UncategorizedInterfaces"
import Select from 'react-select';
import PredictionsModeSwitcher from "@/components/Predictions/PredictionsModeSwitcher"


interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const PastPredictions: React.FC<Props> = ({ record, seasons, selectedSeason }) => {

    const competition = record
    const [useDate, setUseDates] = useState(false);
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>([undefined, undefined]);


    const [baseUri, setBaseUri] = useState('')

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    useEffect(() => {
        if (predictionModes) {
            setPredictionMode(predictionModes[0])
        }
    }, [predictionModes])

    useEffect(() => {

        if (competition) {
            let uri = `admin/competitions/view/${competition.id}/predictions?break_preds=1&type=past`
            if (useDate) {
                uri = uri + `${appendFromToDates(useDate, fromToDates)}`
            } else {
                uri = uri + `&season_id=${selectedSeason ? selectedSeason?.id : ''}`
            }
            setBaseUri(uri)
        }
    }, [competition, fromToDates])

    const [modelDetails, setModelDetails] = useState<Omit<CollectionItemsInterface, 'data'>>()

    return (
        <div>
            {
                competition &&
                <div>
                    <div className="row shadow-sm align-items-center">
                        <div className="col-xl-8">
                            <CompetitionSubHeader actionTitle="Do Predictions" actionButton="doPredictions" record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />
                        </div>
                        <div className="col-xl-4">
                            <PredictionsModeSwitcher predictionMode={predictionMode} predictionModes={predictionModes} setPredictionMode={setPredictionMode} />
                        </div>
                    </div>
                    {baseUri &&
                        <div>
                            <AutoTable key={baseUri} columns={predictionsColumns} baseUri={baseUri} search={true}
                                getModelDetails={setModelDetails}
                                tableId={'competitionPastPredictionsTable'} customModalId="teamModal" />
                            <PredictionStatsTable key={modelDetails?.query || 0} baseUri={`${baseUri}&prediction_mode_id=${predictionMode ? predictionMode.id : 1}&search=${modelDetails?.query || ''}`} />
                        </div>
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

export default PastPredictions