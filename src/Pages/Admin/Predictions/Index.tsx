import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/AutoTable';
import useListSources from '@/hooks/apis/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import "react-datepicker/dist/react-datepicker.css";
import { predictionModes, predictionsColumns } from '@/utils/constants';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import useFromToDates from '@/hooks/useFromToDates';
import PredictionStatsTable from '@/components/Predictions/PredictionStatsTable';
import { useEffect, useState } from 'react';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { PredictionModeInterface } from '../BettingTips/Index';
import Select from 'react-select';

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


    const changePredictionMode = (val: any) => {
        setPredictionMode(val);
    };

    const [modelDetails, setModelDetails] = useState<Omit<CollectionItemsInterface, 'data'>>()

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-md-9">
                                <MatchesPageHeader title={'Predictions List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                            <div className="col-md-3">
                                <div className='d-flex gap-1 align-items-center shadow-sm px-2 rounded'>
                                    <div className='text-nowrap'>Tips Mode:</div> <Select
                                        className="form-control border-0"
                                        classNamePrefix="select"
                                        defaultValue={predictionMode || null}
                                        isDisabled={false}
                                        isLoading={false}
                                        isClearable={false}
                                        isSearchable={false}
                                        placeholder="Select predictions mode"
                                        name='prediction_mode_id'
                                        options={predictionModes || []}
                                        onChange={(v: any) => changePredictionMode(v)}
                                        getOptionValue={(option: any) => `${option['id']}`}
                                        getOptionLabel={(option: any) => option['name']}
                                    />
                                </div>
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
                                    perPage={200}
                                    getModelDetails={setModelDetails}
                                />
                                <PredictionStatsTable key={modelDetails?.query || 0} baseUri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}&q=${modelDetails?.query || ''}`} />
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

