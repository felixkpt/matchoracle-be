import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/AutoTable';
import useListSources from '@/hooks/apis/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import { predictionModes, predictionsColumns } from '@/utils/constants';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import useFromToDates from '@/hooks/useFromToDates';
import PredictionStatsTable from '@/components/Predictions/PredictionStatsTable';
import { useEffect, useState } from 'react';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { PredictionModeInterface } from '@/interfaces/FootballInterface';
import PredictionsModeSwitcher from '@/components/Predictions/PredictionsModeSwitcher';

const Index = () => {

    const { competitions: list_sources } = useListSources()

    const errorsState = useRouteParamValidation();
    const { fromToDates, setFromToDates, baseUri, previousUrl } = useFromToDates(`/admin/predictions/`);

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    useEffect(() => {
        if (predictionModes) {
            setPredictionMode(predictionModes[0])
        }
    }, [predictionModes])

    const [modelDetails, setModelDetails] = useState<Omit<CollectionItemsInterface, 'data'>>()

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-xl-9">
                                <MatchesPageHeader title={'Predictions List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                            <div className="col-xl-3">
                                <PredictionsModeSwitcher predictionMode={predictionMode} predictionModes={predictionModes} setPredictionMode={setPredictionMode} />
                            </div>
                        </div>
                        {
                            predictionMode &&
                            <div key={(predictionMode ? predictionMode.id : 0) + baseUri}>
                                <AutoTable
                                    baseUri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`}
                                    columns={predictionsColumns}
                                    search={true}
                                    list_sources={list_sources}
                                    perPage={100}
                                    getModelDetails={setModelDetails}
                                    tableId='predictionsTable'
                                />
                                <PredictionStatsTable key={modelDetails?.query || 0} baseUri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}&search=${modelDetails?.query || ''}`} />
                            </div>

                        }
                    </div>
                    :
                    <div>
                        {
                            errorsState === 2
                            &&
                            <Error404 previousUrl={previousUrl} currentUrl={location.pathname} />
                        }
                    </div>
            }
        </div>
    );
};

export default Index;

