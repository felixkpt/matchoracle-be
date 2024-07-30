import { PredictionModeInterface, TeamInterface } from "@/interfaces/FootballInterface"
import AutoTable from "@/components/Autos/AutoTable"
import { useEffect, useState } from "react"
import { predictionModes } from '@/utils/constants';
import PredictionsModeSwitcher from "@/components/Predictions/PredictionsModeSwitcher"
import { predictionsColumns } from "@/components/TableColumns"
import PredictionStatsTable from "@/components/Predictions/PredictionStatsTable";

type Props = {
    record: TeamInterface | undefined
}

const Predictions = ({ record }: Props) => {

    const team = record

    const [baseUri, setBaseUri] = useState('')

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    useEffect(() => {
        if (predictionModes) {
            setPredictionMode(predictionModes[0])
        }
    }, [predictionModes])

    useEffect(() => {

        if (team) {
            const uri = `dashboard/teams/view/${team.id}/predictions?include_preds=1`
            
            setBaseUri(uri)
        }
    }, [team])

    return (
        <div>
            {
                team &&
                <div>
                    <div className="row shadow-sm align-items-center">
                        <div className="col-xl-4">
                            <PredictionsModeSwitcher predictionMode={predictionMode} predictionModes={predictionModes} setPredictionMode={setPredictionMode} />
                        </div>
                    </div>
                    {baseUri &&
                        <div>
                            <AutoTable key={baseUri} columns={predictionsColumns} baseUri={baseUri} search={true}
                                tableId={'teamPastPredictionsTable'} customModalId="teamModal" />
                            <PredictionStatsTable key={baseUri} baseUri={`${baseUri}&prediction_mode_id=${predictionMode ? predictionMode.id : 1}`} />
                        </div>
                    }
                </div>
            }
        </div>
    )
}

export default Predictions